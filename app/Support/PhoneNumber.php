<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Extrai apenas os dígitos numéricos.
     */
    public static function clean(?string $phone): string
    {
        if ($phone === null) {
            return '';
        }

        return (string) preg_replace('/\D/', '', $phone);
    }

    /**
     * Valida se é um número telefônico brasileiro válido (fixo ou celular, com DDD).
     * DDD: 11-99
     * 10 dígitos: (XX) XXXX-XXXX (fixo)
     * 11 dígitos: (XX) 9XXXX-XXXX (celular)
     */
    public static function isValidBr(?string $phone): bool
    {
        $digits = self::clean($phone);

        // Se veio com DDI 55 no início e 12-13 dígitos, remove para checar o DDD nacional
        if (str_starts_with($digits, '55') && (strlen($digits) === 12 || strlen($digits) === 13)) {
            $digits = substr($digits, 2);
        }

        $length = strlen($digits);

        if ($length !== 10 && $length !== 11) {
            return false;
        }

        $ddd = (int) substr($digits, 0, 2);
        if ($ddd < 11 || $ddd > 99) {
            return false;
        }

        // DDDs que não existem no Brasil (finais 0 exceto não há, ou listas não atribuídas)
        $invalidDdds = [23, 25, 26, 29, 36, 39, 52, 56, 57, 58, 59, 72, 76, 78, 80];
        if (in_array($ddd, $invalidDdds, true)) {
            return false;
        }

        // Se 11 dígitos, o primeiro dígito após o DDD deve ser 9 (celular)
        if ($length === 11 && $digits[2] !== '9') {
            return false;
        }

        return true;
    }

    /**
     * Formata para padrão internacional E.164 (+55XXXXXXXXXXX).
     */
    public static function toE164(?string $phone): ?string
    {
        $digits = self::clean($phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '55') && (strlen($digits) === 12 || strlen($digits) === 13)) {
            return '+'.$digits;
        }

        if (strlen($digits) === 10 || strlen($digits) === 11) {
            return '+55'.$digits;
        }

        return '+'.$digits;
    }

    /**
     * Formata para exibição amigável brasileira: (11) 91917-0763 ou (11) 3333-4444.
     */
    public static function formatBr(?string $phone): string
    {
        $digits = self::clean($phone);

        if (str_starts_with($digits, '55') && (strlen($digits) === 12 || strlen($digits) === 13)) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 11) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7, 4));
        }

        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 4), substr($digits, 6, 4));
        }

        return $phone ?? '';
    }
}
