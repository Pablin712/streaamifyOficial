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
use App\Services\Donna\Google\DonnaBusinessKnowledgeSheetService;
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
        $plan    = DonnaPlan::where('id', $request->plan_id)->where('is_active', true)->firstOrFail();

        // Google solo es obligatorio para Personal (Business lo usa opcionalmente)
        if ($plan->service_type === 'personal' && !DonnaIntegration::googleConnected($cliente->idcli)) {
            return back()->with('donna_error', 'Debes conectar tu cuenta de Google antes de solicitar Donna Personal.');
        }

        // Solo verifica duplicado del mismo tipo de servicio: Personal no bloquea Business ni viceversa
        $subExiste = DonnaSubscription::where('client_id', $cliente->idcli)
            ->where('service_type', $plan->service_type)
            ->whereIn('status', ['active', 'pending'])
            ->exists();

        $requestExiste = DonnaRequest::where('client_id', $cliente->idcli)
            ->where('status', 'pending')
            ->whereHas('plan', fn ($q) => $q->where('service_type', $plan->service_type))
            ->exists();

        if ($subExiste || $requestExiste) {
            return back()->with('donna_error',
                'Ya tienes una solicitud o suscripción activa para Donna ' . ucfirst($plan->service_type) . '.'
            );
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
        $plan    = DonnaPlan::where('id', $request->plan_id)->where('is_active', true)->firstOrFail();

        // Google solo es obligatorio para Personal
        if ($plan->service_type === 'personal' && !DonnaIntegration::googleConnected($cliente->idcli)) {
            return back()->with('donna_error', 'Debes conectar tu cuenta de Google antes de activar Donna Personal.');
        }

        if ($cliente->saldo < $plan->price) {
            return back()->with('donna_error',
                'Saldo insuficiente. Necesitas $' . number_format($plan->price, 2) .
                ' pero tienes $' . number_format($cliente->saldo, 2) . '.'
            );
        }

        // Busca suscripción activa del MISMO tipo — Personal no bloquea Business ni viceversa
        $subActiva = DonnaSubscription::where('client_id', $cliente->idcli)
            ->where('service_type', $plan->service_type)
            ->where('status', 'active')
            ->where('is_enabled', true)
            ->first();

        // Personal: si la suscripción existe pero falta el canal Telegram, regenerarlo sin cobrar
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
                    ->with('donna_success', 'Tu Donna Personal ya estaba activa. Aquí tienes tu código para vincular Telegram.')
                    ->with('donna_activation_code', $code)
                    ->with('donna_plan_type', 'personal');
            }

            return back()->with('donna_error', 'Ya tienes una suscripción Donna Personal activa con canal configurado.');
        }

        // Business: si ya existe, bloquear
        if ($subActiva) {
            return back()->with('donna_error',
                'Ya tienes una suscripción Donna ' . ucfirst($plan->service_type) . ' activa.'
            );
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
                $businessChannel = DonnaChannel::create([
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

            $response = back()
                ->with('donna_success', $successMsg)
                ->with('donna_activation_code', $activationCode)
                ->with('donna_plan_type', $plan->service_type);

            // Para Business: pasar el channel_id al modal de configuración de WhatsApp
            if ($plan->service_type === 'business' && isset($businessChannel)) {
                $response = $response->with('donna_business_channel_id', $businessChannel->id);
            }

            return $response;

        } catch (\Exception) {
            DB::rollBack();
            return back()->with('donna_error', 'Error al procesar el pago. Intenta nuevamente.');
        }
    }

    public function connectWhatsApp(Request $request)
    {
        $request->validate([
            'channel_id'   => 'required|integer',
            'instance_name'=> 'required|string|max:100',
            'api_base_url' => 'required|url|max:255',
            'api_key'      => 'required|string',
            'phone_number' => 'nullable|string|max:50',
        ]);

        $cliente = Auth::guard('cliente')->user();

        $channel = DonnaChannel::where('id', $request->channel_id)
            ->where('client_id', $cliente->idcli)
            ->where('service_type', 'business')
            ->where('channel_type', 'whatsapp')
            ->firstOrFail();

        $channel->update([
            'instance_name'     => $request->instance_name,
            'api_base_url'      => rtrim($request->api_base_url, '/'),
            'api_key_encrypted' => \Illuminate\Support\Facades\Crypt::encryptString($request->api_key),
            'phone_number'      => $request->phone_number,
            'status'            => 'active',
        ]);

        return back()->with('donna_success', '¡WhatsApp de Donna Business configurado! El equipo verificará la conexión en breve.');
    }

    public function saveConfig(Request $request)
    {
        $request->validate([
            'agent_name'       => 'nullable|string|max:80',
            'personal_context' => 'nullable|string|max:1000',
            'timezone'         => 'nullable|string|max:50',
            'wh_start'         => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'wh_end'           => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'wh_lunch'         => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
        ]);

        $cliente = Auth::guard('cliente')->user();

        $sub = DonnaSubscription::where('client_id', $cliente->idcli)
            ->where('service_type', 'personal')
            ->where('status', 'active')
            ->first();

        if (!$sub) {
            return back()->with('donna_error', 'No tienes una suscripción Donna Personal activa.');
        }

        $wh = array_filter([
            'start' => $request->input('wh_start') ?: null,
            'end'   => $request->input('wh_end') ?: null,
            'lunch' => $request->input('wh_lunch') ?: null,
        ]);

        DonnaAgentConfig::updateOrCreate(
            ['client_id' => $cliente->idcli, 'service_type' => 'personal'],
            [
                'subscription_id'    => $sub->id,
                'agent_name'         => $request->input('agent_name') ?: 'Donna',
                'personal_context'   => $request->input('personal_context') ?: null,
                'timezone'           => $request->input('timezone') ?: 'America/Guayaquil',
                'working_hours_json' => $wh ?: null,
                'is_active'          => true,
            ]
        );

        return back()->with('donna_config_success', 'Configuración de Donna actualizada. Los cambios se aplican en el próximo mensaje.');
    }

    public function saveBusinessConfig(Request $request)
    {
        $request->validate([
            'agent_name'           => 'nullable|string|max:80',
            'business_name'        => 'nullable|string|max:120',
            'business_description' => 'nullable|string|max:2000',
            'tone'                 => 'nullable|string|max:200',
            'language'             => 'nullable|string|max:10',
            'timezone'             => 'nullable|string|max:50',
            'wh_start'             => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'wh_end'               => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'wh_lunch'             => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'main_prompt'          => 'nullable|string|max:5000',
            'wait_seconds'         => 'nullable|integer|min:3|max:60',
            'response_style'       => 'nullable|in:concise,moderate,detailed',
        ]);

        $cliente = Auth::guard('cliente')->user();

        $sub = DonnaSubscription::where('client_id', $cliente->idcli)
            ->where('service_type', 'business')
            ->where('status', 'active')
            ->first();

        if (!$sub) {
            return back()->with('donna_business_error', 'No tienes una suscripción Donna Business activa.');
        }

        $wh = array_filter([
            'start' => $request->input('wh_start') ?: null,
            'end'   => $request->input('wh_end') ?: null,
            'lunch' => $request->input('wh_lunch') ?: null,
        ]);

        $calendarEnabled = (bool) $request->input('calendar_enabled', false);
        $sheetsEnabled   = (bool) $request->input('sheets_enabled', false);

        $config = DonnaAgentConfig::updateOrCreate(
            ['client_id' => $cliente->idcli, 'service_type' => 'business'],
            [
                'subscription_id'      => $sub->id,
                'agent_name'           => $request->input('agent_name') ?: 'Donna',
                'business_name'        => $request->input('business_name') ?: null,
                'business_description' => $request->input('business_description') ?: null,
                'tone'                 => $request->input('tone') ?: 'profesional, amable y directa',
                'language'             => $request->input('language') ?: 'es',
                'timezone'             => $request->input('timezone') ?: 'America/Guayaquil',
                'working_hours_json'   => $wh ?: null,
                'calendar_enabled'     => $calendarEnabled,
                'sheets_enabled'       => $sheetsEnabled,
                'main_prompt'          => $request->input('main_prompt') ?: null,
                'wait_seconds'         => (int) ($request->input('wait_seconds') ?? 35),
                'response_style'       => $request->input('response_style') ?: 'concise',
                'is_active'            => true,
            ]
        );

        // Si se habilitó Sheets y no hay spreadsheet, crear uno con la KB
        if ($sheetsEnabled && !$config->spreadsheet_id) {
            $this->setupBusinessSheet($cliente->idcli, $config);
        }

        return back()->with('donna_business_config_success', 'Configuración de Donna Business actualizada.');
    }

    private function setupBusinessSheet(int $clientId, DonnaAgentConfig $config): void
    {
        $integ = DonnaIntegration::where('client_id', $clientId)
            ->where('integration_type', 'google')
            ->where('status', 'active')
            ->first();

        if (!$integ) return;

        try {
            $token = app(DonnaGoogleTokenService::class)->getValidAccessToken($integ);
            if (!$token) return;

            $businessName = $config->business_name ?? '';
            app(DonnaBusinessKnowledgeSheetService::class)->createAndSync($token, $config, $businessName);
        } catch (\Throwable $e) {
            Log::error('Donna setupBusinessSheet failed', ['client_id' => $clientId, 'error' => $e->getMessage()]);
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
