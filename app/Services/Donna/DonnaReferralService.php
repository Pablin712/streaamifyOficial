<?php

namespace App\Services\Donna;

use App\Models\Cliente;
use App\Models\DonnaReferralEarning;
use App\Models\DonnaReferralPartner;
use App\Models\DonnaSubscription;
use App\Models\Historial;
use Illuminate\Validation\ValidationException;

class DonnaReferralService
{
    /**
     * Valida un código de referido para un cliente que está contratando Donna.
     * Devuelve null si no se ingresó código. Lanza ValidationException si el
     * código no existe, está inactivo, o el cliente intenta auto-referirse.
     */
    public function resolveCode(?string $code, int $referredClientId): ?DonnaReferralPartner
    {
        $code = trim((string) $code);
        if ($code === '') {
            return null;
        }

        $partner = DonnaReferralPartner::active()->whereRaw('UPPER(code) = ?', [strtoupper($code)])->first();

        if (!$partner) {
            throw ValidationException::withMessages(['referral_code' => 'El código de referido no es válido.']);
        }

        if ((int) $partner->client_id === $referredClientId) {
            throw ValidationException::withMessages(['referral_code' => 'No puedes usar tu propio código de referido.']);
        }

        return $partner;
    }

    /**
     * Acredita al partner su comisión por un pago (activación o renovación) y
     * deja registro en el ledger de referidos + historial. Se asume que ya se
     * está dentro de una transacción de BD si el llamador lo requiere.
     */
    public function creditCommission(
        DonnaReferralPartner $partner,
        DonnaSubscription $subscription,
        float $paymentAmount,
        float $commissionPercent,
        string $eventType,
        ?int $empleadoId = null
    ): void {
        $commissionAmount = round($paymentAmount * $commissionPercent / 100, 2);

        Cliente::where('idcli', $partner->client_id)->increment('saldo', $commissionAmount);

        DonnaReferralEarning::create([
            'referral_partner_id' => $partner->id,
            'subscription_id'     => $subscription->id,
            'client_id'           => $subscription->client_id,
            'event_type'          => $eventType,
            'payment_amount'      => $paymentAmount,
            'commission_amount'   => $commissionAmount,
        ]);

        Historial::create([
            'accion'      => 'Comisión de referido Donna acreditada',
            'descripcion' => 'Partner cliente ID: ' . $partner->client_id . ' (código ' . $partner->code . ') | '
                . 'Suscripción #' . $subscription->id . ' | Evento: ' . $eventType . ' | '
                . 'Comisión: ' . $commissionPercent . '% de $' . number_format($paymentAmount, 2) . ' = $' . number_format($commissionAmount, 2),
            'empleado_id' => $empleadoId ?? (\App\Models\Empleado::where('nombreemp', 'Laravel')->value('idemp')),
        ]);
    }
}
