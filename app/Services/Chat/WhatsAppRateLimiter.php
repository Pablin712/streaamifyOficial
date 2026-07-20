<?php

namespace App\Services\Chat;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Guarda "best effort" (no es un límite de seguridad duro, usa el cache de la
 * app -- CACHE_STORE=database tanto local como en producción, sin locks
 * atómicos) para espaciar los envíos salientes de WhatsApp por instancia y
 * evitar patrones de ráfaga que los sistemas antispam de WhatsApp detectan
 * (ver contexto: restricción real de cuenta correlacionada con ráfagas de
 * 11-13 mensajes/minuto en la instancia bot-pagos).
 */
class WhatsAppRateLimiter
{
    public function __construct(private ChatSettingsService $settings)
    {
    }

    /**
     * Si hay lugar dentro de los límites configurados, reserva el turno ahí
     * mismo (queda contado para la próxima llamada) y devuelve allowed=true.
     * Si no, devuelve cuántos segundos faltan para el próximo lugar libre.
     */
    public function check(string $instance): array
    {
        $rateWindow = max(1, (int) $this->settings->get('chat_outbound_rate_window_seconds', 60));
        $burstWindow = max(1, (int) $this->settings->get('chat_outbound_burst_window_seconds', 12));
        $burstLimit = max(1, (int) $this->settings->get('chat_outbound_burst_limit', 4));
        $rateLimit = max(1, (int) $this->settings->get('chat_outbound_rate_limit', 20));

        $now = now();
        $key = $this->cacheKey($instance);

        $timestamps = collect(Cache::get($key, []))
            ->map(fn ($t) => Carbon::parse($t))
            ->filter(fn (Carbon $t) => $t->gt($now->copy()->subSeconds($rateWindow)))
            ->values();

        $inBurst = $timestamps->filter(fn (Carbon $t) => $t->gt($now->copy()->subSeconds($burstWindow)));

        $allowed = $inBurst->count() < $burstLimit && $timestamps->count() < $rateLimit;

        if ($allowed) {
            $timestamps->push($now);
            Cache::put($key, $timestamps->map->toIso8601String()->all(), $rateWindow + 5);

            return ['allowed' => true, 'retry_after' => 0];
        }

        $retryAfter = $inBurst->count() >= $burstLimit
            ? $burstWindow - $now->diffInSeconds($inBurst->min())
            : $rateWindow - $now->diffInSeconds($timestamps->min());

        return ['allowed' => false, 'retry_after' => max(1, $retryAfter)];
    }

    private function cacheKey(string $instance): string
    {
        return 'chat:outbound:sends:'.$instance;
    }
}
