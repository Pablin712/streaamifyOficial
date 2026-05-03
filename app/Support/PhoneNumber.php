<?php

namespace App\Support;

class PhoneNumber
{
    public static function digits(?string $raw): string
    {
        $value = trim((string) $raw);

        if ($value === '') {
            return '';
        }

        if (str_contains($value, '@')) {
            $value = preg_replace('/@.+$/', '', $value) ?? $value;
        }

        return preg_replace('/\D+/', '', $value) ?? '';
    }

    public static function canonicalEc(?string $raw): ?string
    {
        $digits = self::digits($raw);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '5930') && strlen($digits) >= 13) {
            $digits = '593' . substr($digits, 4);
        }

        if (str_starts_with($digits, '593') && strlen($digits) >= 12) {
            return substr($digits, 0, 12);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            return '593' . substr($digits, 1);
        }

        if (strlen($digits) === 9 && str_starts_with($digits, '9')) {
            return '593' . $digits;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '593')) {
            return $digits;
        }

        return $digits;
    }

    public static function formatForStorage(?string $raw): string
    {
        $canonical = self::canonicalEc($raw);

        if ($canonical === null || $canonical === '') {
            return '';
        }

        if (str_starts_with($canonical, '593') && strlen($canonical) === 12) {
            return sprintf(
                '+593 %s %s %s',
                substr($canonical, 3, 2),
                substr($canonical, 5, 3),
                substr($canonical, 8, 4)
            );
        }

        return '+' . $canonical;
    }

    public static function toWhatsappJid(?string $raw): string
    {
        $value = trim((string) $raw);

        if ($value === '') {
            return '';
        }

        if (str_contains($value, '@')) {
            return $value;
        }

        $canonical = self::canonicalEc($value);

        if ($canonical === null || $canonical === '') {
            return '';
        }

        return $canonical . '@s.whatsapp.net';
    }
}
