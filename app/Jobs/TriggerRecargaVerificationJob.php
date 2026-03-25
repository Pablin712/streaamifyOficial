<?php

namespace App\Jobs;

use App\Models\Recarga;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TriggerRecargaVerificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(public int $idrec)
    {
    }

    public function handle(): void
    {
        $webhookUrl = config('services.n8n.payment_webhook');

        if (empty($webhookUrl)) {
            Log::warning('Webhook de n8n no configurado para verificacion de recargas', [
                'idrec' => $this->idrec,
            ]);
            return;
        }

        $recarga = Recarga::with(['cliente', 'banco', 'estado'])->find($this->idrec);

        if (!$recarga || (int) $recarga->idestado !== 1) {
            return;
        }

        $payload = [
            'event' => 'recarga.created',
            'idrec' => $recarga->idrec,
            'idcli' => $recarga->idcli,
            'idban' => $recarga->idban,
            'banco_nombre' => optional($recarga->banco)->nombreban,
            'numcomprobante' => $recarga->numcomprobante,
            'valor' => (float) $recarga->valor,
            'recarga_url' => url('/api/v2/payments/n8n/recargas/' . $recarga->idrec),
            'foto_url' => url('/api/v2/payments/n8n/recargas/' . $recarga->idrec . '/comprobante'),
            'created_at' => optional($recarga->created_at)->toIso8601String(),
            'trace_id' => 'recarga-' . $recarga->idrec . '-' . now()->format('Ymd-His'),
        ];

        Log::info('Disparando webhook de verificacion de recarga', [
            'idrec' => $recarga->idrec,
            'webhook' => $webhookUrl,
            'trace_id' => $payload['trace_id'],
        ]);

        $request = Http::acceptJson()
            ->timeout(8)
            ->retry(2, 300);

        $webhookSecret = config('services.n8n.payment_webhook_secret');
        if (!empty($webhookSecret)) {
            $request = $request->withHeaders([
                'X-Webhook-Secret' => $webhookSecret,
            ]);
        }

        $response = $request->post($webhookUrl, $payload);

        Log::info('Respuesta webhook de verificacion de recarga', [
            'idrec' => $recarga->idrec,
            'method' => 'POST',
            'status' => $response->status(),
        ]);

        // Fallback: algunos webhooks en n8n quedan configurados como GET.
        if (
            $response->status() === 404
            && str_contains(strtolower($response->body()), 'not registered for post')
        ) {
            Log::warning('Webhook n8n no acepta POST, intentando fallback GET', [
                'idrec' => $recarga->idrec,
                'webhook' => $webhookUrl,
            ]);

            $response = $request->get($webhookUrl, $payload);

            Log::info('Respuesta webhook de verificacion de recarga', [
                'idrec' => $recarga->idrec,
                'method' => 'GET',
                'status' => $response->status(),
            ]);
        }

        if (!$response->successful()) {
            Log::error('Webhook n8n respondio con error al disparar verificacion de recarga', [
                'idrec' => $recarga->idrec,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Webhook n8n fallo con status ' . $response->status());
        }
    }
}
