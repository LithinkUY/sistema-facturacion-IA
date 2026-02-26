<?php

namespace App\Utils;

use Illuminate\Support\Str;

class NumberToWords
{
    public static function toSpanishCurrency(float $amount, string $currencyLabel = 'pesos uruguayos'): string
    {
        $normalized = number_format($amount, 2, '.', '');
        [$integer, $decimal] = explode('.', $normalized);

        $integerWords = self::convert((int) $integer);
        $integerWords = self::normalizeEnding($integerWords);
        $decimalPart = str_pad($decimal, 2, '0', STR_PAD_RIGHT);

        $phrase = trim($integerWords.' '.$currencyLabel);
        $phrase .= ' con '.$decimalPart.'/100';

        return Str::ucfirst($phrase);
    }

    protected static function convert(int $number): string
    {
        if ($number === 0) {
            return 'cero';
        }

        if ($number < 1000) {
            return self::convertHundreds($number);
        }

        if ($number < 1000000) {
            $thousands = intdiv($number, 1000);
            $rest = $number % 1000;
            $thousandsText = $thousands === 1 ? 'mil' : self::convertHundreds($thousands).' mil';

            return trim($thousandsText.' '.self::convertHundreds($rest));
        }

        $millions = intdiv($number, 1000000);
        $rest = $number % 1000000;
        $millionsText = $millions === 1 ? 'un millón' : self::convert($millions).' millones';

        if ($rest === 0) {
            return $millionsText;
        }

        if ($rest < 1000) {
            return trim($millionsText.' '.self::convertHundreds($rest));
        }

        return trim($millionsText.' '.self::convert($rest));
    }

    protected static function convertHundreds(int $number): string
    {
        $units = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
        $teens = [
            10 => 'diez',
            11 => 'once',
            12 => 'doce',
            13 => 'trece',
            14 => 'catorce',
            15 => 'quince',
            16 => 'dieciséis',
            17 => 'diecisiete',
            18 => 'dieciocho',
            19 => 'diecinueve',
        ];
        $tens = [
            2 => 'veinte',
            3 => 'treinta',
            4 => 'cuarenta',
            5 => 'cincuenta',
            6 => 'sesenta',
            7 => 'setenta',
            8 => 'ochenta',
            9 => 'noventa',
        ];
        $veinti = [
            21 => 'veintiún',
            22 => 'veintidós',
            23 => 'veintitrés',
            24 => 'veinticuatro',
            25 => 'veinticinco',
            26 => 'veintiséis',
            27 => 'veintisiete',
            28 => 'veintiocho',
            29 => 'veintinueve',
        ];
        $hundreds = [
            1 => 'ciento',
            2 => 'doscientos',
            3 => 'trescientos',
            4 => 'cuatrocientos',
            5 => 'quinientos',
            6 => 'seiscientos',
            7 => 'setecientos',
            8 => 'ochocientos',
            9 => 'novecientos',
        ];

        if ($number === 0) {
            return '';
        }

        if ($number < 10) {
            return $units[$number];
        }

        if ($number < 20) {
            return $teens[$number];
        }

        if ($number < 100) {
            if (array_key_exists($number, $veinti)) {
                return $veinti[$number];
            }
            if ($number === 20) {
                return 'veinte';
            }
            $ten = intdiv($number, 10);
            $unit = $number % 10;
            $text = $tens[$ten];

            return $unit === 0 ? $text : $text.' y '.$units[$unit];
        }

        if ($number === 100) {
            return 'cien';
        }

        $hundred = intdiv($number, 100);
        $rest = $number % 100;
        $text = $hundreds[$hundred];

        return $rest === 0 ? $text : $text.' '.self::convertHundreds($rest);
    }

    protected static function normalizeEnding(string $words): string
    {
        $words = trim($words);

        return preg_replace('/uno$/', 'un', $words);
    }
}
