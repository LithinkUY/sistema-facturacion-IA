<?php

namespace App\Services\Cfe;

use Illuminate\Support\Facades\Log;

class RutService
{
    public function __construct(private RutLookupService $lookupService)
    {
    }

    public function isValidRUT(?string $rut): bool
    {
        if (empty($rut)) {
            return false;
        }

        return $this->lookupService->isValidRut($rut);
    }

    /**
     * Busca datos de la empresa/persona por RUT usando fuentes públicas.
     * Mantiene compatibilidad con el código existente.
     */
    public function lookup(string $rut): ?array
    {
        $result = $this->lookupService->lookup($rut);

        if ($result === null) {
            return null;
        }

        // Devolver en el formato que esperaba el código antiguo
        return [
            'rut'          => $result['rut'],
            'businessName' => $result['razon_social'],
            'tradeName'    => $result['nombre_comercial'] ?? $result['razon_social'],
            'city'         => $result['ciudad'] ?? 'Montevideo',
            'department'   => $result['departamento'] ?? 'Montevideo',
        ];
    }
}
