<?php

namespace App\Services;

use App\Models\Cuenta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NetflixCodigoService
{
    public function isEligibleCuenta(?Cuenta $cuenta): bool
    {
        if (!$cuenta) {
            return false;
        }

        $cuenta->loadMissing('valor.proveedor', 'valor.servicio');

        $serviceId = Str::upper((string) ($cuenta->valor->idser ?? ''));
        $providerName = $this->normalizeText($cuenta->valor->proveedor->nombrepro ?? '');

        return $serviceId === $this->getTargetServiceId()
            && $providerName === $this->normalizeText($this->getTargetProviderName());
    }

    public function getTargetProviderName(): string
    {
        return (string) config('services.netflix_code.provider_name', 'Alejandro Guevara');
    }

    public function getTargetServiceId(): string
    {
        return Str::upper((string) config('services.netflix_code.service_id', 'NETFLIX'));
    }

    public function getDefaultExpiresMinutes(): int
    {
        return (int) config('services.netflix_code.code_expires_minutes', 15);
    }

    public function requestCode(Cuenta $cuenta, array $requestedBy): array
    {
        $cuenta->loadMissing('valor.proveedor', 'valor.servicio');
        $normalizedPhone = $this->normalizePhone($requestedBy['phone'] ?? null);

        if ($normalizedPhone !== null) {
            $requestedBy['phone'] = $normalizedPhone;
        }

        if (!$this->isEligibleCuenta($cuenta)) {
            return [
                'success' => false,
                'message' => 'Esta cuenta no es elegible para pedir codigo de Netflix con el proveedor configurado.',
            ];
        }

        $webhookUrl = (string) config('services.n8n.netflix_code_webhook');
        if ($webhookUrl === '') {
            return [
                'success' => false,
                'message' => 'No esta configurado el webhook para pedir codigo de Netflix.',
            ];
        }

        $payload = [
            'event' => 'netflix.request_code',
            'trace_id' => 'netflix-code-' . $cuenta->idcue . '-' . now()->format('Ymd-His'),
            'cuenta' => [
                'idcue' => $cuenta->idcue,
                'usuario' => $cuenta->usuariocue,
                'contrasena' => $cuenta->contrasenacue,
                'servicio_id' => $cuenta->valor->idser ?? null,
                'servicio' => $cuenta->valor->servicio->nombreser ?? ($cuenta->valor->idser ?? null),
                'proveedor' => $cuenta->valor->proveedor->nombrepro ?? null,
            ],
            'requested_by' => $requestedBy,
            'requested_at' => now()->toIso8601String(),
        ];

        if (($requestedBy['type'] ?? null) === 'cliente') {
            $payload['cliente'] = [
                'idcli' => $requestedBy['id'] ?? null,
                'nombre' => $requestedBy['name'] ?? null,
                'email' => $requestedBy['email'] ?? null,
                'telefono' => $normalizedPhone,
                'pais' => $requestedBy['country'] ?? null,
            ];
        }

        try {
            $requestN8n = Http::acceptJson()
                ->timeout((int) config('services.netflix_code.timeout_seconds', 70))
                ->retry(0, 0);

            $webhookSecret = config('services.n8n.payment_webhook_secret');
            if (!empty($webhookSecret)) {
                $requestN8n = $requestN8n->withHeaders([
                    'X-Webhook-Secret' => $webhookSecret,
                ]);
            }

            $response = $requestN8n->post($webhookUrl, $payload);

            if (!$response->successful()) {
                Log::warning('Webhook pedir codigo Netflix devolvio error', [
                    'idcue' => $cuenta->idcue,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => 'No se pudo obtener el codigo de Netflix desde el webhook.',
                ];
            }

            return $this->normalizeWebhookResponse($response->json(), $response->body());
        } catch (\Throwable $e) {
            Log::error('Error solicitando codigo de Netflix', [
                'idcue' => $cuenta->idcue,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Ocurrio un error al esperar la respuesta del codigo de Netflix.',
            ];
        }
    }

    private function normalizeWebhookResponse($jsonBody, string $rawBody): array
    {
        $data = is_array($jsonBody) ? $jsonBody : [];

        $code = $data['codigo']
            ?? $data['code']
            ?? $data['pin']
            ?? $data['otp']
            ?? null;

        if (!$code && preg_match('/\b(\d{4})\b/', $rawBody, $matches)) {
            $code = $matches[1];
        }

        $expiresMinutes = (int) ($data['expires_in_minutes'] ?? $data['expira_en_minutos'] ?? $this->getDefaultExpiresMinutes());
        $expiresAt = $data['expires_at']
            ?? now()->addMinutes($expiresMinutes)->toIso8601String();

        if (!$code) {
            return [
                'success' => false,
                'message' => $data['message'] ?? $data['mensaje'] ?? 'El webhook respondio pero no devolvio un codigo valido.',
            ];
        }

        return [
            'success' => true,
            'message' => $data['message'] ?? $data['mensaje'] ?? 'Codigo de Netflix obtenido correctamente.',
            'code' => (string) $code,
            'expires_in_minutes' => $expiresMinutes,
            'expires_at' => $expiresAt,
        ];
    }

    private function normalizeText(?string $value): string
    {
        return Str::lower(trim((string) Str::ascii((string) $value)));
    }

    private function normalizePhone(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits !== '' ? $digits : null;
    }
}
