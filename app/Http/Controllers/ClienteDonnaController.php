<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\DonnaPlan;
use App\Models\DonnaRequest;
use App\Models\DonnaSubscription;
use App\Models\DonnaIntegration;
use App\Models\Empleado;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClienteDonnaController extends Controller
{
    public function solicitar(Request $request)
    {
        $request->validate(['plan_id' => 'required|exists:donna_plans,id']);

        $cliente = Auth::guard('cliente')->user();

        if (!DonnaIntegration::googleConnected($cliente->idcli)) {
            return back()->with('donna_error', 'Debes conectar tu cuenta de Google antes de solicitar Donna.');
        }

        $plan = DonnaPlan::where('id', $request->plan_id)->where('is_active', true)->firstOrFail();

        $yaExiste = DonnaSubscription::where('client_id', $cliente->idcli)
            ->whereIn('status', ['active', 'pending'])
            ->exists()
            || DonnaRequest::where('client_id', $cliente->idcli)
            ->where('status', 'pending')
            ->exists();

        if ($yaExiste) {
            return back()->with('donna_error', 'Ya tienes una solicitud o suscripción activa para Donna.');
        }

        DonnaRequest::create([
            'client_id' => $cliente->idcli,
            'plan_id'   => $plan->id,
            'status'    => 'pending',
            'message'   => $request->input('message'),
        ]);

        return back()->with('donna_success', '¡Solicitud enviada! El equipo de Streamify se pondrá en contacto contigo pronto para activar Donna.');
    }

    public function activar(Request $request)
    {
        $request->validate(['plan_id' => 'required|exists:donna_plans,id']);

        $cliente = Auth::guard('cliente')->user();

        if (!DonnaIntegration::googleConnected($cliente->idcli)) {
            return back()->with('donna_error', 'Debes conectar tu cuenta de Google antes de activar Donna.');
        }

        $plan = DonnaPlan::where('id', $request->plan_id)->where('is_active', true)->firstOrFail();

        if ($cliente->saldo < $plan->price) {
            return back()->with('donna_error',
                'Saldo insuficiente. Necesitas $' . number_format($plan->price, 2) .
                ' pero tienes $' . number_format($cliente->saldo, 2) . '.'
            );
        }

        $yaActivo = DonnaSubscription::where('client_id', $cliente->idcli)
            ->where('status', 'active')
            ->where('is_enabled', true)
            ->exists();

        if ($yaActivo) {
            return back()->with('donna_error', 'Ya tienes una suscripción Donna activa.');
        }

        DB::beginTransaction();
        try {
            $now = now();
            $expiresAt = match($plan->billing_cycle) {
                'monthly'  => $now->copy()->addMonth(),
                'yearly'   => $now->copy()->addYear(),
                default    => null,
            };

            DonnaSubscription::create([
                'client_id'       => $cliente->idcli,
                'plan_id'         => $plan->id,
                'service_type'    => $plan->service_type,
                'status'          => 'active',
                'billing_cycle'   => $plan->billing_cycle,
                'price_paid'      => $plan->price,
                'currency'        => $plan->currency,
                'starts_at'       => $now,
                'expires_at'      => $expiresAt,
                'last_payment_at' => $now,
                'is_enabled'      => true,
            ]);

            Cliente::where('idcli', $cliente->idcli)->decrement('saldo', $plan->price);

            $sistemaEmp = Empleado::where('nombreemp', 'Laravel')->value('idemp');

            Historial::create([
                'accion'      => 'Donna activada (autoservicio)',
                'descripcion' => $cliente->nombrecli . ' activó ' . $plan->name . ' por $' . number_format($plan->price, 2),
                'empleado_id' => $sistemaEmp,
            ]);

            DB::commit();

            return back()->with('donna_success',
                '¡Donna activada exitosamente! Tu suscripción está activa' .
                ($expiresAt ? ' hasta el ' . $expiresAt->format('d/m/Y') : '') . '.'
            );
        } catch (\Exception) {
            DB::rollBack();
            return back()->with('donna_error', 'Error al procesar el pago. Intenta nuevamente.');
        }
    }
}
