<?php

namespace App\Services\Cfe;

use Illuminate\Support\Facades\Log;

class RutService
{
    private const MOCK_RUT_DATABASE = [
        '210000370010' => [
            'rut' => '210000370010',
            'businessName' => 'ADMINISTRACIÓN NACIONAL DE TELECOMUNICACIONES',
            'tradeName' => 'ANTEL',
            'city' => 'Montevideo',
            'department' => 'Montevideo',
        ],
        '100674900010' => [
            'rut' => '100674900010',
            'businessName' => 'RUBÉN NICOLÁS GÓMEZ MÉNDEZ',
            'tradeName' => 'Gómez Méndez',
            'city' => 'San Carlos',
            'department' => 'Maldonado',
        ],
    ];

    public function isValidRUT(?string $rut): bool
    {
        if (empty($rut)) {
            return false;
        }

        $clean = preg_replace('/[^0-9]/', '', $rut);
        if (strlen($clean) !== 12) {
            Log::warning('RUT inválido: longitud incorrecta', ['rut' => $rut]);

            return false;
        }

        $verificationDigit = (int) substr($clean, -1);
        $calculated = $this->calculateVerificationDigit($clean);

        if ($verificationDigit === $calculated) {
            return true;
        }

        Log::warning('RUT inválido: dígito verificador incorrecto', ['rut' => $rut]);

        return false;
    }

    public function lookup(string $rut): ?array
    {
        $clean = preg_replace('/[^0-9]/', '', $rut);

        return self::MOCK_RUT_DATABASE[$clean] ?? null;
    }

    private function calculateVerificationDigit(string $rut): int
    {
        $digits = str_split(substr($rut, 0, -1));
        $weights = [4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;

        foreach ($digits as $index => $digit) {
            $sum += (int) $digit * $weights[$index];
        }

        $mod = 11 - ($sum % 11);

        if ($mod === 11) {
            return 0;
        }

        if ($mod === 10) {
            return 1;
        }

        return $mod;
    }
}
