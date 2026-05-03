<?php

namespace App\Support;

class ClienteAuth
{
    public const MAX_FULL_NAME_LENGTH = 50;
    public const MAX_PHONE_LENGTH = 50;
    public const MAX_COUNTRY_LENGTH = 30;

    public static function normalizeText(?string $value): string
    {
        return preg_replace('/\s+/u', ' ', trim((string) $value)) ?? '';
    }

    public static function normalizeName(?string $value): string
    {
        $normalized = self::normalizeText($value);

        if ($normalized === '') {
            return '';
        }

        return mb_convert_case($normalized, MB_CASE_TITLE, 'UTF-8');
    }

    public static function buildFullName(?string $firstName = null, ?string $lastName = null, ?string $fullName = null): string
    {
        if ($fullName !== null) {
            return self::normalizeName($fullName);
        }

        return self::normalizeName(trim(self::normalizeText($firstName) . ' ' . self::normalizeText($lastName)));
    }

    public static function normalizePhone(?string $phone): string
    {
        return PhoneNumber::formatForStorage($phone);
    }

    public static function phoneDigits(?string $phone): string
    {
        return PhoneNumber::canonicalEc($phone) ?? '';
    }

    public static function passwordRules(bool $confirmed = true): array
    {
        $rules = [
            'required',
            'string',
            'min:6',
            'regex:/[0-9]/',
            'regex:/[@$!%*?&]/',
        ];

        if ($confirmed) {
            $rules[] = 'confirmed';
        }

        return $rules;
    }

    public static function passwordMessages(): array
    {
        return [
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.regex' => 'La contraseña debe contener al menos un número y un símbolo especial (@$!%*?&).',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    public static function fullNameTooLongMessage(): string
    {
        return 'El nombre completo no puede superar los 50 caracteres.';
    }
}
