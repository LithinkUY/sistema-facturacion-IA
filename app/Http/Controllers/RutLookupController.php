<?php

namespace App\Http\Controllers;

use App\Services\Cfe\RutLookupService;
use Illuminate\Http\Request;

/**
 * Endpoint para buscar datos de empresa/persona por RUT uruguayo.
 * Usado por el formulario de contactos vía AJAX.
 */
class RutLookupController extends Controller
{
    public function __construct(private RutLookupService $rutLookup)
    {
    }

    /**
     * GET /api/rut/{rut}
     * Devuelve los datos de la empresa/persona para ese RUT.
     */
    public function lookup(string $rut)
    {
        $clean = preg_replace('/[^0-9]/', '', $rut);

        if (empty($clean)) {
            return response()->json([
                'success' => false,
                'message' => 'RUT vacío',
            ], 400);
        }

        // Validar formato
        if (strlen($clean) === 12 && ! $this->rutLookup->isValidRut($clean)) {
            return response()->json([
                'success' => false,
                'message' => 'RUT inválido: el dígito verificador no corresponde.',
            ], 422);
        }

        $data = $this->rutLookup->lookup($clean);

        if ($data === null) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron datos para ese RUT en los registros públicos. Podés ingresar los datos manualmente.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
