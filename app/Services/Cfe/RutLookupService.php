<?php

namespace App\Services\Cfe;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para consultar datos de una empresa/persona por RUT uruguayo.
 *
 * Fuentes consultadas en orden:
 *   1. Cache local (24 hs) para no saturar servicios externos
 *   2. API BPS (Banco de Previsión Social) - servicio público de Uruguay
 *   3. Scraping de la página de consulta de DGI
 *   4. Fallback: solo devuelve validación del dígito verificador
 */
class RutLookupService
{
    private Client $http;

    // BPS expone un servicio REST público sin autenticación
    private const BPS_API_URL = 'https://servicios.bps.gub.uy/ConsultaEmpleadores/rest/empleador/';

    // Fallback: página de consulta DGI (scraping básico)
    private const DGI_QUERY_URL = 'https://www.dgi.gub.uy/wdgi/RA?accion=28&rut=';

    public function __construct(?Client $client = null)
    {
        $this->http = $client ?? new Client([
            'timeout'         => 8,
            'connect_timeout' => 5,
            'verify'          => false,   // DGI / BPS usan certificados autofirmados a veces
            'headers'         => [
                'Accept'          => 'application/json, text/html',
                'Accept-Language' => 'es-UY,es;q=0.9',
                'User-Agent'      => 'Mozilla/5.0 (compatible; SistemaFacturacion/1.0)',
            ],
        ]);
    }

    /**
     * Busca los datos de una empresa/persona por RUT.
     * Devuelve array con los campos o null si no se encontró.
     *
     * @return array{
     *   rut: string,
     *   razon_social: string,
     *   nombre_comercial: string|null,
     *   direccion: string|null,
     *   ciudad: string|null,
     *   departamento: string|null,
     *   email: string|null,
     *   telefono: string|null,
     *   tipo_doc: string,
     *   fuente: string,
     * }|null
     */
    public function lookup(string $rut): ?array
    {
        $clean = preg_replace('/[^0-9]/', '', $rut);

        if (empty($clean)) {
            return null;
        }

        // Validar dígito verificador primero
        if (strlen($clean) === 12 && ! $this->isValidRut($clean)) {
            return null;
        }

        // Revisar cache para no hacer llamadas repetidas
        $cacheKey = 'rut_lookup_' . $clean;
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            Log::info('RUT lookup desde cache', ['rut' => $clean]);
            return $cached;
        }

        $result = null;

        // --- Fuente 1: API BPS (solo RUT de 12 dígitos = empresas) ---
        if (strlen($clean) === 12) {
            $result = $this->lookupFromBPS($clean);
        }

        // --- Fuente 2: Scraping DGI si BPS no respondió ---
        if ($result === null) {
            $result = $this->lookupFromDGI($clean);
        }

        // Guardar en cache 24 horas si se encontró algo
        if ($result !== null) {
            Cache::put($cacheKey, $result, now()->addHours(24));
        }

        return $result;
    }

    // =========================================================================
    // Fuente 1: API BPS
    // =========================================================================

    private function lookupFromBPS(string $rut): ?array
    {
        try {
            // El RUT para BPS es sin el último dígito verificador en algunos endpoints,
            // pero la mayoría acepta el RUT completo de 12 dígitos.
            $url = self::BPS_API_URL . $rut;

            $response = $this->http->get($url);
            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                return null;
            }

            $body = (string) $response->getBody();
            $data = json_decode($body, true);

            if (empty($data) || ! isset($data['razonSocial'])) {
                return null;
            }

            Log::info('RUT encontrado en BPS', ['rut' => $rut, 'razon_social' => $data['razonSocial']]);

            return $this->normalizeBPSResponse($rut, $data);

        } catch (RequestException $e) {
            Log::warning('Error consultando BPS', ['rut' => $rut, 'error' => $e->getMessage()]);
            return null;
        } catch (\Exception $e) {
            Log::warning('Error inesperado consultando BPS', ['rut' => $rut, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function normalizeBPSResponse(string $rut, array $data): array
    {
        return [
            'rut'             => $rut,
            'razon_social'    => $this->cleanString($data['razonSocial'] ?? ''),
            'nombre_comercial'=> $this->cleanString($data['nombreComercial'] ?? $data['razonSocial'] ?? ''),
            'direccion'       => $this->cleanString($data['direccion'] ?? $data['domicilio'] ?? ''),
            'ciudad'          => $this->cleanString($data['ciudad'] ?? $data['localidad'] ?? 'Montevideo'),
            'departamento'    => $this->normalizeDepartment($data['departamento'] ?? 'Montevideo'),
            'email'           => $data['email'] ?? null,
            'telefono'        => $data['telefono'] ?? null,
            'tipo_doc'        => 'RUT',
            'fuente'          => 'BPS',
        ];
    }

    // =========================================================================
    // Fuente 2: Scraping básico DGI
    // =========================================================================

    private function lookupFromDGI(string $rut): ?array
    {
        try {
            // Intentar endpoint JSON alternativo de DGI
            $url = 'https://www.dgi.gub.uy/wdgi/RA?accion=28&rut=' . urlencode($rut);

            $response = $this->http->get($url, [
                'timeout' => 6,
            ]);

            $body = (string) $response->getBody();

            // DGI devuelve HTML; buscamos el nombre de la empresa
            if (preg_match('/Raz[oó]n Social[^:]*:\s*<[^>]*>([^<]+)</i', $body, $matches)) {
                $razonSocial = $this->cleanString($matches[1]);

                if (! empty($razonSocial) && strtolower($razonSocial) !== 'no existe') {
                    Log::info('RUT encontrado en DGI scraping', ['rut' => $rut]);

                    // Intentar extraer más datos del HTML
                    $direccion = '';
                    $departamento = 'Montevideo';

                    if (preg_match('/Domicilio[^:]*:\s*<[^>]*>([^<]+)</i', $body, $m)) {
                        $direccion = $this->cleanString($m[1]);
                    }
                    if (preg_match('/Departamento[^:]*:\s*<[^>]*>([^<]+)</i', $body, $m)) {
                        $departamento = $this->normalizeDepartment($this->cleanString($m[1]));
                    }

                    return [
                        'rut'             => $rut,
                        'razon_social'    => $razonSocial,
                        'nombre_comercial'=> $razonSocial,
                        'direccion'       => $direccion,
                        'ciudad'          => $departamento,
                        'departamento'    => $departamento,
                        'email'           => null,
                        'telefono'        => null,
                        'tipo_doc'        => strlen($rut) === 12 ? 'RUT' : 'CI',
                        'fuente'          => 'DGI',
                    ];
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::warning('Error consultando DGI', ['rut' => $rut, 'error' => $e->getMessage()]);
            return null;
        }
    }

    // =========================================================================
    // Validación dígito verificador RUT uruguayo
    // =========================================================================

    public function isValidRut(string $rut): bool
    {
        $clean = preg_replace('/[^0-9]/', '', $rut);

        if (strlen($clean) !== 12) {
            return false;
        }

        $verif     = (int) substr($clean, -1);
        $digits    = str_split(substr($clean, 0, 11));
        $weights   = [4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum       = 0;

        foreach ($digits as $i => $d) {
            $sum += (int) $d * $weights[$i];
        }

        $mod = 11 - ($sum % 11);
        if ($mod === 11) $mod = 0;
        if ($mod === 10) $mod = 1;

        return $verif === $mod;
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function cleanString(string $str): string
    {
        return trim(preg_replace('/\s+/', ' ', html_entity_decode($str, ENT_QUOTES, 'UTF-8')));
    }

    private function normalizeDepartment(string $dep): string
    {
        $departments = [
            'ARTIGAS'        => 'Artigas',
            'CANELONES'      => 'Canelones',
            'CERRO LARGO'    => 'Cerro Largo',
            'COLONIA'        => 'Colonia',
            'DURAZNO'        => 'Durazno',
            'FLORES'         => 'Flores',
            'FLORIDA'        => 'Florida',
            'LAVALLEJA'      => 'Lavalleja',
            'MALDONADO'      => 'Maldonado',
            'MONTEVIDEO'     => 'Montevideo',
            'PAYSANDU'       => 'Paysandú',
            'PAYSANDÚ'       => 'Paysandú',
            'RIO NEGRO'      => 'Río Negro',
            'RÍO NEGRO'      => 'Río Negro',
            'RIVERA'         => 'Rivera',
            'ROCHA'          => 'Rocha',
            'SALTO'          => 'Salto',
            'SAN JOSE'       => 'San José',
            'SAN JOSÉ'       => 'San José',
            'SORIANO'        => 'Soriano',
            'TACUAREMBO'     => 'Tacuarembó',
            'TACUAREMBÓ'     => 'Tacuarembó',
            'TREINTA Y TRES' => 'Treinta y Tres',
        ];

        $upper = mb_strtoupper(trim($dep), 'UTF-8');
        return $departments[$upper] ?? ucwords(mb_strtolower($dep, 'UTF-8'));
    }
}
