<?php

namespace App\Services\Cfe;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para integración directa con DGI Uruguay
 */
class DgiApiService
{
    private Client $httpClient;

    private const ENDPOINTS = [
        'testing' => [
            'auth' => 'https://cfetesting.dgi.gub.uy/api/auth',
            'submit' => 'https://cfetesting.dgi.gub.uy/api/cfe',
        ],
        'production' => [
            'auth' => 'https://cfe.dgi.gub.uy/api/auth',
            'submit' => 'https://cfe.dgi.gub.uy/api/cfe',
        ],
    ];

    public function __construct(?Client $client = null)
    {
        $this->httpClient = $client ?? new Client([
            'timeout' => (float) config('cfe.http_timeout', 30),
            'verify' => (bool) config('cfe.verify_ssl', false),
        ]);
    }

    public function getDGIToken(string $certificatePath, string $password, bool $isProduction = false): string
    {
        $environment = $isProduction ? 'production' : 'testing';
        $authUrl = self::ENDPOINTS[$environment]['auth'];
        $rut = config('cfe.dgi_user_rut');

        if (empty($rut)) {
            throw new Exception('No se configuró CFE_DGI_USER_RUT en .env');
        }

        try {
            $certificateContent = file_get_contents($certificatePath);

            $response = $this->httpClient->post($authUrl, [
                'multipart' => [
                    [
                        'name' => 'certificate',
                        'contents' => $certificateContent,
                        'filename' => basename($certificatePath),
                    ],
                    [
                        'name' => 'password',
                        'contents' => $password,
                    ],
                    [
                        'name' => 'rut',
                        'contents' => $rut,
                    ],
                ],
            ]);

            $payload = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() === 200 && isset($payload['token'])) {
                Log::info('Token DGI obtenido exitosamente');

                return $payload['token'];
            }

            throw new Exception($payload['mensaje'] ?? $payload['error'] ?? 'Error obteniendo token DGI');
        } catch (Exception $exception) {
            Log::error('Error obteniendo token DGI', ['message' => $exception->getMessage()]);

            throw new Exception('Error autenticación DGI: ' . $exception->getMessage());
        }
    }

    public function submitCFEToDGI(string $xmlContent, string $token, bool $isProduction = false): array
    {
        $environment = $isProduction ? 'production' : 'testing';
        $submitUrl = self::ENDPOINTS[$environment]['submit'];

        try {
            $response = $this->httpClient->post($submitUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/xml',
                ],
                'body' => $xmlContent,
            ]);

            $payload = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() === 200 && isset($payload['success']) && $payload['success']) {
                Log::info('CFE enviado exitosamente a DGI');

                return $payload;
            }

            return [
                'success' => false,
                'errors' => $payload['errors'] ?? ['Error desconocido al enviar a DGI'],
            ];
        } catch (Exception $exception) {
            Log::error('Error enviando CFE a DGI', ['message' => $exception->getMessage()]);

            return [
                'success' => false,
                'errors' => ['Error al enviar a DGI: ' . $exception->getMessage()],
            ];
        }
    }
}
