<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoicePdfService
{
    /**
     * Generar PDF de factura para una transacción existente del sistema
     *
     * @param int $transactionId
     * @return array ['success' => bool, 'path' => string, 'filename' => string, 'error' => string]
     */
    public function generateFromTransaction($transactionId)
    {
        try {
            $transaction = DB::table('transactions')
                ->where('id', $transactionId)
                ->first();

            if (!$transaction) {
                return ['success' => false, 'error' => 'Transacción no encontrada'];
            }

            // Obtener datos del negocio
            $business = DB::table('business')
                ->where('id', $transaction->business_id)
                ->first();

            // Obtener ubicación
            $location = DB::table('business_locations')
                ->where('id', $transaction->location_id)
                ->first();

            // Obtener contacto/cliente
            $contact = DB::table('contacts')
                ->where('id', $transaction->contact_id)
                ->first();

            // Obtener líneas de venta con productos
            $sellLines = DB::table('transaction_sell_lines as tsl')
                ->join('products as p', 'tsl.product_id', '=', 'p.id')
                ->join('variations as v', 'tsl.variation_id', '=', 'v.id')
                ->where('tsl.transaction_id', $transactionId)
                ->select(
                    'p.name as product_name',
                    'p.sku',
                    'v.name as variation_name',
                    'v.sub_sku',
                    'tsl.quantity',
                    'tsl.unit_price',
                    'tsl.unit_price_before_discount',
                    'tsl.line_discount_type',
                    'tsl.line_discount_amount',
                    'tsl.item_tax',
                    DB::raw('(tsl.quantity * tsl.unit_price) as line_total')
                )
                ->get();

            $invoiceData = [
                'invoice_no' => $transaction->invoice_no ?? 'N/A',
                'date' => $transaction->transaction_date,
                'status' => $transaction->status,
                'payment_status' => $transaction->payment_status ?? 'due',
                'subtotal' => $transaction->total_before_tax,
                'tax' => $transaction->tax_amount ?? 0,
                'discount' => $transaction->discount_amount ?? 0,
                'discount_type' => $transaction->discount_type ?? 'fixed',
                'shipping' => $transaction->shipping_charges ?? 0,
                'total' => $transaction->final_total,
                'notes' => $transaction->additional_notes ?? '',
                'business_name' => $business->name ?? 'Empresa',
                'business_rut' => $business->tax_number_1 ?? '',
                'business_address' => $location ? trim(($location->landmark ?? '') . ' ' . ($location->city ?? '') . ' ' . ($location->state ?? '')) : '',
                'business_phone' => $location->mobile ?? ($location->alternate_number ?? ''),
                'business_email' => $business->email ?? '',
                'client_name' => $contact->name ?? 'Cliente',
                'client_rut' => $contact->tax_number ?? '',
                'client_address' => $contact->address_line_2 ?? ($contact->address_line_1 ?? ''),
                'client_phone' => $contact->mobile ?? '',
                'client_email' => $contact->email ?? '',
                'items' => $sellLines->toArray(),
            ];

            return $this->generatePdf($invoiceData);

        } catch (\Exception $e) {
            Log::error('Error generando PDF de transacción: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Generar PDF de factura/presupuesto desde un pedido de WhatsApp
     * (cuando el pedido aún no es una transacción formal)
     *
     * @param array $orderData Datos del pedido
     * @return array
     */
    public function generateFromWhatsAppOrder(array $orderData)
    {
        try {
            $businessId = $orderData['business_id'] ?? 1;
            $business = DB::table('business')->where('id', $businessId)->first();
            $location = DB::table('business_locations')
                ->where('business_id', $businessId)
                ->first();

            // Construir ítems desde los datos del pedido
            $items = [];
            $subtotal = 0;
            foreach ($orderData['items'] ?? [] as $item) {
                $lineTotal = ($item['quantity'] ?? 1) * ($item['price'] ?? 0);
                $subtotal += $lineTotal;
                $items[] = (object) [
                    'product_name' => $item['name'] ?? 'Producto',
                    'sku' => $item['sku'] ?? '',
                    'variation_name' => '',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['price'] ?? 0,
                    'line_total' => $lineTotal,
                    'item_tax' => 0,
                    'line_discount_amount' => 0,
                ];
            }

            $tax = $orderData['tax'] ?? 0;
            $discount = $orderData['discount'] ?? 0;
            $total = $subtotal + $tax - $discount;

            $invoiceNumber = 'WA-' . date('Ymd') . '-' . substr(uniqid(), -5);

            $invoiceData = [
                'invoice_no' => $invoiceNumber,
                'date' => date('Y-m-d H:i:s'),
                'status' => 'presupuesto',
                'payment_status' => 'pendiente',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'discount_type' => 'fixed',
                'shipping' => 0,
                'total' => $total,
                'notes' => $orderData['notes'] ?? 'Pedido generado por WhatsApp',
                'business_name' => $business->name ?? 'Empresa',
                'business_rut' => $business->tax_number_1 ?? '',
                'business_address' => $location ? trim(($location->landmark ?? '') . ' ' . ($location->city ?? '') . ' ' . ($location->state ?? '')) : '',
                'business_phone' => $location->mobile ?? ($location->alternate_number ?? ''),
                'business_email' => $business->email ?? '',
                'client_name' => $orderData['client_name'] ?? 'Cliente',
                'client_rut' => $orderData['client_rut'] ?? '',
                'client_address' => $orderData['client_address'] ?? '',
                'client_phone' => $orderData['client_phone'] ?? '',
                'client_email' => $orderData['client_email'] ?? '',
                'items' => $items,
                'is_quote' => true,
            ];

            return $this->generatePdf($invoiceData);

        } catch (\Exception $e) {
            Log::error('Error generando PDF de pedido WhatsApp: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Generar el PDF con mPDF
     *
     * @param array $data
     * @return array
     */
    protected function generatePdf(array $data)
    {
        $html = $this->buildInvoiceHtml($data);

        $tempDir = public_path('uploads/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => $tempDir,
            'mode' => 'utf-8',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'margin_top' => 10,
            'margin_bottom' => 15,
            'margin_left' => 12,
            'margin_right' => 12,
            'format' => 'A4',
        ]);

        $mpdf->useSubstitutions = true;
        $mpdf->SetTitle('Factura-' . $data['invoice_no']);
        $mpdf->SetAuthor($data['business_name']);

        // Marca de agua suave
        $mpdf->SetWatermarkText($data['business_name'], 0.06);
        $mpdf->showWatermarkText = true;

        $mpdf->WriteHTML($html);

        // Guardar en storage temporal
        $storageDir = storage_path('app/invoices');
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $filename = 'Factura-' . $data['invoice_no'] . '.pdf';
        $filepath = $storageDir . '/' . $filename;
        $mpdf->Output($filepath, 'F');

        return [
            'success' => true,
            'path' => $filepath,
            'filename' => $filename,
            'invoice_no' => $data['invoice_no'],
        ];
    }

    /**
     * Generar HTML para la factura
     */
    protected function buildInvoiceHtml(array $data)
    {
        $isQuote = !empty($data['is_quote']);
        $title = $isQuote ? 'PRESUPUESTO' : 'FACTURA';

        $date = date('d/m/Y H:i', strtotime($data['date']));
        $currency = '$';

        $html = '
        <style>
            body { font-family: Arial, sans-serif; color: #333; font-size: 12px; }
            .header { background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%); color: white; padding: 20px; border-radius: 8px; }
            .header h1 { margin: 0; font-size: 28px; }
            .header .subtitle { font-size: 14px; opacity: 0.9; }
            .info-section { margin-top: 15px; }
            .info-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 12px; margin-bottom: 10px; }
            .info-box h3 { margin: 0 0 8px 0; color: #1a5276; font-size: 13px; border-bottom: 2px solid #2980b9; padding-bottom: 4px; }
            .info-box p { margin: 2px 0; font-size: 11px; }
            .items-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
            .items-table thead th { background: #1a5276; color: white; padding: 8px 10px; text-align: left; font-size: 11px; }
            .items-table tbody td { padding: 7px 10px; border-bottom: 1px solid #dee2e6; font-size: 11px; }
            .items-table tbody tr:nth-child(even) { background: #f8f9fa; }
            .items-table .right { text-align: right; }
            .items-table .center { text-align: center; }
            .totals-table { width: 280px; margin-left: auto; margin-top: 15px; }
            .totals-table td { padding: 5px 10px; font-size: 12px; }
            .totals-table .label { text-align: right; color: #666; }
            .totals-table .value { text-align: right; font-weight: bold; }
            .totals-table .total-row td { border-top: 2px solid #1a5276; font-size: 16px; color: #1a5276; padding-top: 8px; }
            .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 10px; font-weight: bold; }
            .badge-paid { background: #d4edda; color: #155724; }
            .badge-due { background: #fff3cd; color: #856404; }
            .badge-quote { background: #d1ecf1; color: #0c5460; }
            .notes-box { background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 10px; margin-top: 15px; }
            .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #dee2e6; padding-top: 10px; }
            .stamp { text-align: right; margin-top: 20px; }
            .two-col { width: 100%; }
            .two-col td { vertical-align: top; width: 50%; }
        </style>';

        // ============ ENCABEZADO ============
        $html .= '
        <div class="header">
            <table style="width:100%">
                <tr>
                    <td style="width:60%">
                        <h1>' . htmlspecialchars($data['business_name']) . '</h1>
                        <div class="subtitle">';
        if (!empty($data['business_rut'])) {
            $html .= 'RUT: ' . htmlspecialchars($data['business_rut']) . ' | ';
        }
        if (!empty($data['business_phone'])) {
            $html .= 'Tel: ' . htmlspecialchars($data['business_phone']) . ' | ';
        }
        if (!empty($data['business_email'])) {
            $html .= htmlspecialchars($data['business_email']);
        }
        $html .= '</div>';
        if (!empty($data['business_address'])) {
            $html .= '<div class="subtitle">' . htmlspecialchars($data['business_address']) . '</div>';
        }
        $html .= '
                    </td>
                    <td style="width:40%; text-align:right;">
                        <h1 style="font-size:24px;">' . $title . '</h1>
                        <div class="subtitle">N° ' . htmlspecialchars($data['invoice_no']) . '</div>
                        <div class="subtitle">' . $date . '</div>
                    </td>
                </tr>
            </table>
        </div>';

        // ============ DATOS CLIENTE + FACTURA ============
        $html .= '
        <div class="info-section">
            <table class="two-col">
                <tr>
                    <td style="padding-right:10px;">
                        <div class="info-box">
                            <h3>📋 Datos del Cliente</h3>
                            <p><strong>' . htmlspecialchars($data['client_name']) . '</strong></p>';
        if (!empty($data['client_rut'])) {
            $html .= '<p>RUT: ' . htmlspecialchars($data['client_rut']) . '</p>';
        }
        if (!empty($data['client_address'])) {
            $html .= '<p>Dir: ' . htmlspecialchars($data['client_address']) . '</p>';
        }
        if (!empty($data['client_phone'])) {
            $html .= '<p>Tel: ' . htmlspecialchars($data['client_phone']) . '</p>';
        }
        if (!empty($data['client_email'])) {
            $html .= '<p>Email: ' . htmlspecialchars($data['client_email']) . '</p>';
        }
        $html .= '
                        </div>
                    </td>
                    <td style="padding-left:10px;">
                        <div class="info-box">
                            <h3>📄 Datos del Documento</h3>
                            <p><strong>Número:</strong> ' . htmlspecialchars($data['invoice_no']) . '</p>
                            <p><strong>Fecha:</strong> ' . $date . '</p>
                            <p><strong>Estado:</strong> ';

        if ($isQuote) {
            $html .= '<span class="badge badge-quote">PRESUPUESTO</span>';
        } elseif (($data['payment_status'] ?? '') === 'paid') {
            $html .= '<span class="badge badge-paid">PAGADO</span>';
        } else {
            $html .= '<span class="badge badge-due">PENDIENTE</span>';
        }

        $html .= '</p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>';

        // ============ TABLA DE PRODUCTOS ============
        $html .= '
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:5%">#</th>
                    <th style="width:40%">Producto / Servicio</th>
                    <th style="width:10%" class="center">Cant.</th>
                    <th style="width:15%" class="right">P. Unit.</th>
                    <th style="width:15%" class="right">Desc.</th>
                    <th style="width:15%" class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>';

        $idx = 0;
        foreach ($data['items'] as $item) {
            $idx++;
            $item = (object) $item;
            $name = $item->product_name;
            if (!empty($item->variation_name) && $item->variation_name !== 'DUMMY') {
                $name .= ' - ' . $item->variation_name;
            }
            $sku = !empty($item->sku) ? ' (' . $item->sku . ')' : '';
            $qty = number_format($item->quantity, 0);
            $unitPrice = $currency . number_format($item->unit_price, 2, ',', '.');
            $discount = ($item->line_discount_amount ?? 0) > 0
                ? $currency . number_format($item->line_discount_amount, 2, ',', '.')
                : '-';
            $lineTotal = $currency . number_format($item->line_total, 2, ',', '.');

            $html .= '
                <tr>
                    <td class="center">' . $idx . '</td>
                    <td>' . htmlspecialchars($name) . '<small style="color:#999;">' . htmlspecialchars($sku) . '</small></td>
                    <td class="center">' . $qty . '</td>
                    <td class="right">' . $unitPrice . '</td>
                    <td class="right">' . $discount . '</td>
                    <td class="right">' . $lineTotal . '</td>
                </tr>';
        }

        $html .= '
            </tbody>
        </table>';

        // ============ TOTALES ============
        $html .= '
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal:</td>
                <td class="value">' . $currency . number_format($data['subtotal'], 2, ',', '.') . '</td>
            </tr>';

        if (($data['discount'] ?? 0) > 0) {
            $discountLabel = $data['discount_type'] === 'percentage'
                ? 'Descuento (' . $data['discount'] . '%):'
                : 'Descuento:';
            $html .= '
            <tr>
                <td class="label">' . $discountLabel . '</td>
                <td class="value">-' . $currency . number_format($data['discount'], 2, ',', '.') . '</td>
            </tr>';
        }

        if (($data['tax'] ?? 0) > 0) {
            $html .= '
            <tr>
                <td class="label">IVA:</td>
                <td class="value">' . $currency . number_format($data['tax'], 2, ',', '.') . '</td>
            </tr>';
        }

        if (($data['shipping'] ?? 0) > 0) {
            $html .= '
            <tr>
                <td class="label">Envío:</td>
                <td class="value">' . $currency . number_format($data['shipping'], 2, ',', '.') . '</td>
            </tr>';
        }

        $html .= '
            <tr class="total-row">
                <td class="label"><strong>TOTAL:</strong></td>
                <td class="value"><strong>' . $currency . number_format($data['total'], 2, ',', '.') . '</strong></td>
            </tr>
        </table>';

        // ============ NOTAS ============
        if (!empty($data['notes'])) {
            $html .= '
            <div class="notes-box">
                <strong>📝 Notas:</strong><br>
                ' . nl2br(htmlspecialchars($data['notes'])) . '
            </div>';
        }

        // ============ VALIDEZ (para presupuestos) ============
        if ($isQuote) {
            $html .= '
            <div style="margin-top:15px; padding:10px; background:#e8f4fd; border:1px solid #b8daff; border-radius:6px; font-size:11px;">
                <strong>ℹ️ Este presupuesto tiene una validez de 15 días</strong> a partir de la fecha de emisión.
                Los precios pueden variar fuera de este período.
            </div>';
        }

        // ============ FIRMA ============
        $html .= '
        <div class="stamp">
            <table style="width:100%; margin-top:30px;">
                <tr>
                    <td style="width:50%"></td>
                    <td style="width:50%; text-align:center; border-top: 1px solid #333; padding-top:5px;">
                        <small>' . htmlspecialchars($data['business_name']) . '</small>
                    </td>
                </tr>
            </table>
        </div>';

        // ============ PIE DE PÁGINA ============
        $html .= '
        <div class="footer">
            <p>Documento generado automáticamente por ' . htmlspecialchars($data['business_name']) . '</p>
            <p>Gracias por su preferencia 🙏</p>
        </div>';

        return $html;
    }

    /**
     * Buscar la última venta de un cliente por teléfono
     */
    public function findLastSaleByPhone($phone, $businessId = 1)
    {
        $last8 = substr(preg_replace('/[^0-9]/', '', $phone), -8);

        $contact = DB::table('contacts')
            ->where('business_id', $businessId)
            ->where(function ($q) use ($last8, $phone) {
                $q->where('mobile', 'LIKE', "%{$last8}%")
                  ->orWhere('alternate_number', 'LIKE', "%{$last8}%");
            })
            ->first();

        if (!$contact) {
            return null;
        }

        return DB::table('transactions')
            ->where('business_id', $businessId)
            ->where('contact_id', $contact->id)
            ->where('type', 'sell')
            ->orderBy('transaction_date', 'desc')
            ->first();
    }

    /**
     * Buscar venta por número de factura
     */
    public function findSaleByInvoiceNo($invoiceNo, $businessId = 1)
    {
        return DB::table('transactions')
            ->where('business_id', $businessId)
            ->where('invoice_no', $invoiceNo)
            ->where('type', 'sell')
            ->first();
    }

    /**
     * Buscar las últimas ventas de un contacto
     */
    public function getRecentSalesForContact($contactId, $businessId = 1, $limit = 5)
    {
        return DB::table('transactions')
            ->where('business_id', $businessId)
            ->where('contact_id', $contactId)
            ->where('type', 'sell')
            ->orderBy('transaction_date', 'desc')
            ->limit($limit)
            ->get();
    }
}
