<?php

namespace App\Services\Cfe;

use App\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class CFEService
{
    public function __construct(
        private DigitalSignatureService $signatureService,
        private DgiApiService $dgiService,
        private CFEXmlGenerator $xmlGenerator,
        private RutService $rutService
    ) {
    }

    /**
     * Procesa un CFE para una transacción de venta existente.
     */
    public function processTransaction(Transaction $transaction, array $options = []): array
    {
        try {
            $transaction->loadMissing([
                'contact',
                'business.currency',
                'location',
                'sell_lines.product',
                'sell_lines.sub_unit',
            ]);

            $validation = $this->validateTransaction($transaction);

            if (! $validation['isValid']) {
                return [
                    'success' => false,
                    'errors' => $validation['errors'],
                    'warnings' => $validation['warnings'],
                ];
            }

            $cfeData = $this->convertTransactionToCFEData($transaction);
            $xmlContent = $this->xmlGenerator->generateCFE($cfeData);

            $xmlValidation = $this->xmlGenerator->validateCFE($xmlContent);
            if (! $xmlValidation['valid']) {
                return [
                    'success' => false,
                    'errors' => $xmlValidation['errors'],
                ];
            }

            $environment = $options['environment'] ?? config('cfe.environment', 'testing');
            $certificatePath = $options['certificate_path'] ?? config('cfe.certificate_path');
            $certificatePassword = $options['certificate_password'] ?? config('cfe.certificate_password');
            $autoSubmit = (bool) ($options['auto_submit'] ?? config('cfe.auto_submit', true));
            $isProduction = $environment === 'production';

            $signedXml = $xmlContent;
            $signatureInfo = null;

            if ($certificatePath && $certificatePassword && file_exists($certificatePath)) {
                $signedResult = $this->signatureService->signCFE($xmlContent, [
                    'certificate' => file_get_contents($certificatePath),
                    'password' => $certificatePassword,
                ]);
                $signedXml = $signedResult['signedXML'];
                $signatureInfo = $signedResult['certificateInfo'];
            }

            $response = [
                'success' => true,
                'warnings' => array_merge($validation['warnings'], $xmlValidation['errors']),
                'xmlContent' => $xmlContent,
                'signedXml' => $signedXml,
                'signatureInfo' => $signatureInfo,
                'environment' => $environment,
            ];

            if ($autoSubmit && $certificatePath && $certificatePassword && file_exists($certificatePath)) {
                try {
                    $token = $this->dgiService->getDGIToken($certificatePath, $certificatePassword, $isProduction);
                    $dgiResponse = $this->dgiService->submitCFEToDGI($signedXml, $token, $isProduction);
                    $response = array_merge($response, $dgiResponse);
                } catch (Exception $dgiException) {
                    $response['success'] = false;
                    $response['errors'] = ['Error al enviar a DGI: ' . $dgiException->getMessage()];
                }
            } else {
                $response['warnings'][] = 'CFE generado pero no enviado (falta certificado o envío deshabilitado).';
            }

            $this->saveTrackingInfo($transaction, $response);

            return $response;
        } catch (Exception $exception) {
            Log::error('Error procesando CFE', [
                'transaction_id' => $transaction->id,
                'message' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'errors' => ['Error procesando CFE: ' . $exception->getMessage()],
            ];
        }
    }

    private function validateTransaction(Transaction $transaction): array
    {
        $errors = [];
        $warnings = [];
        $contact = $transaction->contact;
        $emitterRut = $this->getEmitterRut($transaction);

        if (empty($emitterRut)) {
            $errors[] = 'RUT del emisor requerido';
        } elseif (! $this->rutService->isValidRUT($emitterRut)) {
            $errors[] = 'RUT del emisor inválido';
        }

        if (! $contact || empty($contact->name)) {
            $errors[] = 'Nombre del receptor requerido';
        }

        if ($contact && ! empty($contact->tax_number) && ! $this->rutService->isValidRUT($contact->tax_number)) {
            $warnings[] = 'RUT del cliente inválido, se enviará como e-Ticket.';
        }

        if ($transaction->sell_lines->isEmpty()) {
            $errors[] = 'La venta debe tener al menos un item';
        }

        if (empty($transaction->final_total) || $transaction->final_total <= 0) {
            $errors[] = 'El total debe ser mayor a 0';
        }

        return [
            'isValid' => count($errors) === 0,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    private function convertTransactionToCFEData(Transaction $transaction): array
    {
        $contact = $transaction->contact;
        $location = $transaction->location;
        $business = $transaction->business;
        $currency = optional($business->currency);

        return [
            'tipo' => (int) config('cfe.default_cfe_type', 111),
            'serie' => config('cfe.default_series', 'A'),
            'numero' => $this->extractNumericInvoice($transaction),
            'fecha' => Carbon::parse($transaction->transaction_date)->format('Y-m-d'),
            'fecha_vencimiento' => Carbon::parse($transaction->transaction_date)->format('Y-m-d'),
            'forma_pago' => $transaction->payment_status === 'paid' ? 1 : 2,
            'emisor' => [
                'rut' => $this->getEmitterRut($transaction),
                'razonSocial' => $business->name,
                'nombreComercial' => $business->landmark ?? $business->name,
                'direccion' => $location->landmark ?? '',
                'ciudad' => $location->city ?? 'Montevideo',
                'departamento' => $location->state ?? 'Montevideo',
            ],
            'receptor' => [
                'tipoDoc' => $this->guessContactDocumentType($contact),
                'documento' => $this->getCustomerDocument($contact),
                'nombre' => $contact->name ?? 'Consumidor Final',
                'direccion' => $contact->landmark ?? '',
                'ciudad' => $contact->city ?? 'Montevideo',
                'departamento' => $contact->state ?? 'Montevideo',
            ],
            'items' => $this->mapItems($transaction),
            'totales' => [
                'moneda' => $currency->code ?? 'UYU',
                'tipo_cambio' => (float) ($transaction->exchange_rate ?? 1),
                'no_gravado' => 0,
                'iva_tasa_min' => 0,
                'iva_tasa_basica' => (float) ($transaction->tax_amount ?? 0),
                'total' => (float) $transaction->final_total,
            ],
        ];
    }

    private function mapItems(Transaction $transaction): array
    {
        return $transaction->sell_lines->map(function ($line) {
            return [
                'name' => optional($line->product)->name ?? __('lang_v1.item'),
                'description' => $line->sell_line_note ?? optional($line->product)->sku ?? '',
                'quantity' => (float) $line->quantity,
                'unitPrice' => (float) $line->unit_price_inc_tax,
                'unit' => optional($line->sub_unit)->short_name ?? 'unidad',
            ];
        })->toArray();
    }

    private function guessContactDocumentType(?object $contact): string
    {
        if (! $contact) {
            return 'CI';
        }

        $document = $this->getCustomerDocument($contact);

        return strlen($document) === 12 ? 'RUT' : 'CI';
    }

    private function getCustomerDocument(?object $contact): string
    {
        if (! $contact) {
            return '0';
        }

        return preg_replace('/[^0-9A-Za-z]/', '', $contact->tax_number ?? $contact->mobile ?? '0');
    }

    private function getEmitterRut(Transaction $transaction): ?string
    {
        if ($configured = config('cfe.emitter_rut')) {
            return $configured;
        }

        $business = $transaction->business;

        return $business->tax_number_1 ?? $business->tax_number ?? null;
    }

    private function extractNumericInvoice(Transaction $transaction): int
    {
        $invoiceNo = $transaction->invoice_no;

        if (empty($invoiceNo)) {
            return (int) $transaction->id;
        }

        $numbers = preg_replace('/[^0-9]/', '', $invoiceNo);

        return (int) ($numbers ?: $transaction->id ?: 1);
    }

    private function saveTrackingInfo(Transaction $transaction, array $data): void
    {
        $tracking = [
            'status' => $data['success'] ? 'submitted' : 'error',
            'trackingCode' => $data['trackingCode'] ?? ($data['cae'] ?? null),
            'environment' => $data['environment'] ?? config('cfe.environment', 'testing'),
            'timestamp' => now()->toIso8601String(),
        ];

        Log::info('Resultado CFE', [
            'transaction_id' => $transaction->id,
            'response' => $data,
        ]);

        $transaction->cfe_status = $tracking['status'];
        $transaction->cfe_track_id = $tracking['trackingCode'];
        $transaction->cfe_last_response = $data;
        $transaction->save();
    }
}
