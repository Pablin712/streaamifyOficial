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
     *
     * Además de los límites de ráfaga/sostenido, exige un espaciado mínimo
     * aleatorio desde el último envío reservado -- bajo el cupo permitido,
     * varios empleados podían mandar casi al mismo tiempo; esta regla fuerza
     * que la entrega efectiva salga de a una, con demora random entre envíos
     * (mismo espíritu que el delay random 20-90s que ya usa el flujo n8n de
     * cobros masivos para sus propios destinatarios).
     */
    public function check(string $instance): array
    {
        $rateWindow = max(1, (int) $this->settings->get('chat_outbound_rate_window_seconds', 60));
        $burstWindow = max(1, (int) $this->settings->get('chat_outbound_burst_window_seconds', 12));
        $burstLimit = max(1, (int) $this->settings->get('chat_outbound_burst_limit', 4));
        $rateLimit = max(1, (int) $this->settings->get('chat_outbound_rate_limit', 20));
        $minGap = max(0, (int) $this->settings->get('chat_outbound_min_gap_seconds', 2));
        $maxGap = max($minGap, (int) $this->settings->get('chat_outbound_max_gap_seconds', 6));

        $now = now();
        $key = $this->cacheKey($instance);

        $timestamps = collect(Cache::get($key, []))
            ->map(fn ($t) => Carbon::parse($t))
            ->filter(fn (Carbon $t) => $t->gt($now->copy()->subSeconds($rateWindow)))
            ->values();

        $inBurst = $timestamps->filter(fn (Carbon $t) => $t->gt($now->copy()->subSeconds($burstWindow)));

        $lastSend = $timestamps->max();
        $requiredGap = random_int($minGap, $maxGap);
        // diffInSeconds() en Carbon 3 devuelve un valor CON SIGNO por defecto
        // (negativo cuando el otro momento es pasado) -- hay que pedir el
        // absoluto explícitamente, si no esta cuenta de "cuánto pasó desde el
        // último envío" sale invertida.
        $gapOk = ! $lastSend || $now->diffInSeconds($lastSend, true) >= $requiredGap;

        $allowed = $gapOk && $inBurst->count() < $burstLimit && $timestamps->count() < $rateLimit;

        if ($allowed) {
            $timestamps->push($now);
            Cache::put($key, $timestamps->map->toIso8601String()->all(), $rateWindow + 5);

            return ['allowed' => true, 'retry_after' => 0];
        }

        $retryAfterCandidates = [];

        if (! $gapOk) {
            $retryAfterCandidates[] = $requiredGap - $now->diffInSeconds($lastSend, true);
        }
        if ($inBurst->count() >= $burstLimit) {
            $retryAfterCandidates[] = $burstWindow - $now->diffInSeconds($inBurst->min(), true);
        }
        if ($timestamps->count() >= $rateLimit) {
            $retryAfterCandidates[] = $rateWindow - $now->diffInSeconds($timestamps->min(), true);
        }

        return ['allowed' => false, 'retry_after' => max(1, (int) ceil(max($retryAfterCandidates)))];
    }

    /**
     * Espera (acotado) a que haya turno en la instancia, para envíos sueltos
     * disparados desde un request HTTP normal (botones de Cuentas/Usuarios)
     * que no tienen una cola propia como el chat. Devuelve false si se agotó
     * el presupuesto de espera sin conseguir turno -- el caller debe avisar
     * "probá de nuevo en un momento" en vez de mandar igual o colgar el request.
     */
    public function awaitSlot(string $instance, int $maxWaitSeconds): bool
    {
        $deadline = microtime(true) + max(0, $maxWaitSeconds);

        while (true) {
            $result = $this->check($instance);

            if ($result['allowed']) {
                return true;
            }

            $remaining = $deadline - microtime(true);

            if ($remaining <= 0) {
                return false;
            }

            sleep((int) max(1, min($result['retry_after'], $remaining)));
        }
    }

    private function cacheKey(string $instance): string
    {
        return 'chat:outbound:sends:'.$instance;
    }
}
