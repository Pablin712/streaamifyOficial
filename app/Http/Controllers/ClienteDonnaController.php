<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\DonnaAgentConfig;
use App\Models\DonnaChannel;
use App\Models\DonnaPlan;
use App\Models\DonnaRequest;
use App\Models\DonnaSubscription;
use App\Models\DonnaIntegration;
use App\Models\Empleado;
use App\Models\Historial;
use App\Services\Donna\Google\DonnaGoogleTokenService;
use App\Services\Donna\Google\DonnaSpreadsheetSetupService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

        $subActiva = DonnaSubscription::where('client_id', $cliente->idcli)
            ->where('status', 'active')
            ->where('is_enabled', true)
            ->first();

        // Si hay suscripción activa pero sin canal Telegram, generar el canal/código sin cobrar
        if ($subActiva && $plan->service_type === 'personal') {
            $canalExiste = DonnaChannel::where('subscription_id', $subActiva->id)
                ->where('channel_type', 'telegram')
                ->exists();

            if (!$canalExiste) {
                $code = strtoupper(Str::random(6));
                DonnaChannel::create([
                    'client_id'       => $cliente->idcli,
                    'subscription_id' => $subActiva->id,
                    'service_type'    => 'personal',
                    'channel_type'    => 'telegram',
                    'provider'        => 'telegram_bot',
                    'status'          => 'pending',
                    'activation_code' => $code,
                    'is_default'      => true,
                ]);

                $this->setupSpreadsheet($cliente->idcli, $subActiva->id);

                return back()
                    ->with('donna_success', 'Tu Donna ya estaba activa. Aquí tienes tu código para vincular Telegram.')
                    ->with('donna_activation_code', $code)
                    ->with('donna_plan_type', 'personal');
            }

            return back()->with('donna_error', 'Ya tienes una suscripción Donna activa con canal configurado.');
        }

        if ($subActiva) {
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

            $sub = DonnaSubscription::create([
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

            $activationCode = null;

            if ($plan->service_type === 'personal') {
                // Código único para que el cliente registre su Telegram chat_id
                $activationCode = strtoupper(Str::random(6));
                DonnaChannel::create([
                    'client_id'       => $cliente->idcli,
                    'subscription_id' => $sub->id,
                    'service_type'    => 'personal',
                    'channel_type'    => 'telegram',
                    'provider'        => 'telegram_bot',
                    'status'          => 'pending',
                    'activation_code' => $activationCode,
                    'is_default'      => true,
                ]);
            } elseif ($plan->service_type === 'business') {
                DonnaChannel::create([
                    'client_id'       => $cliente->idcli,
                    'subscription_id' => $sub->id,
                    'service_type'    => 'business',
                    'channel_type'    => 'whatsapp',
                    'provider'        => 'evolution_api',
                    'status'          => 'pending',
                    'is_default'      => true,
                    'metadata_json'   => ['requires_manual_setup' => true],
                ]);
            }

            Cliente::where('idcli', $cliente->idcli)->decrement('saldo', $plan->price);

            $sistemaEmp = Empleado::where('nombreemp', 'Laravel')->value('idemp');

            Historial::create([
                'accion'      => 'Donna activada (autoservicio)',
                'descripcion' => $cliente->nombrecli . ' activó ' . $plan->name . ' por $' . number_format($plan->price, 2),
                'empleado_id' => $sistemaEmp,
            ]);

            DB::commit();

            if ($plan->service_type === 'personal') {
                $this->setupSpreadsheet($cliente->idcli, $sub->id);
            }

            $successMsg = '¡Donna activada exitosamente! Tu suscripción está activa' .
                ($expiresAt ? ' hasta el ' . $expiresAt->format('d/m/Y') : '') . '.';

            return back()
                ->with('donna_success', $successMsg)
                ->with('donna_activation_code', $activationCode)
                ->with('donna_plan_type', $plan->service_type);

        } catch (\Exception) {
            DB::rollBack();
            return back()->with('donna_error', 'Error al procesar el pago. Intenta nuevamente.');
        }
    }

    private function setupSpreadsheet(int $clientId, int $subscriptionId): void
    {
        // Solo actúa si no hay spreadsheet configurado aún
        $yaConfigurado = DonnaAgentConfig::where('client_id', $clientId)
            ->where('service_type', 'personal')
            ->whereNotNull('spreadsheet_id')
            ->exists();

        if ($yaConfigurado) {
            return;
        }

        $integ = DonnaIntegration::where('client_id', $clientId)
            ->where('integration_type', 'google')
            ->where('status', 'active')
            ->first();

        if (!$integ) {
            return;
        }

        try {
            $token = app(DonnaGoogleTokenService::class)->getValidAccessToken($integ);

            if (!$token) {
                return;
            }

            $result = app(DonnaSpreadsheetSetupService::class)->createTasksSpreadsheet($token);

            if (!$result['success']) {
                return;
            }

            DonnaAgentConfig::updateOrCreate(
                ['client_id' => $clientId, 'service_type' => 'personal'],
                [
                    'subscription_id'  => $subscriptionId,
                    'spreadsheet_id'   => $result['spreadsheet_id'],
                    'spreadsheet_name' => 'Tareas',
                    'is_active'        => true,
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Donna setupSpreadsheet failed', ['client_id' => $clientId, 'error' => $e->getMessage()]);
        }
    }
}
