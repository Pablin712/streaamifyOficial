<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DonnaChannel;
use App\Models\DonnaIntegration;
use App\Models\DonnaPlan;
use App\Models\DonnaSubscription;
use App\Models\DonnaRequest;
use App\Models\Cliente;
use App\Models\Historial;
use App\Services\Donna\DonnaReferralService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class DonnaSubscriptionController extends Controller
{
    public function index()
    {
        if (!Gate::allows('donna.suscripciones')) {
            abort(403);
        }

        $suscripciones = DonnaSubscription::with(['plan', 'cliente', 'activatedByEmployee'])
            ->orderByDesc('created_at')
            ->get();

        $planes   = DonnaPlan::orderBy('name')->get();
        $clientes = Cliente::orderBy('nombrecli')->get();

        $integraciones = DonnaIntegration::where('integration_type', 'google')
            ->whereIn('client_id', $suscripciones->pluck('client_id')->unique())
            ->latest()
            ->get()
            ->keyBy('client_id');

        return view('donna.suscripciones.index', compact('suscripciones', 'planes', 'clientes', 'integraciones'));
    }

    public function store(Request $request)
    {
        if (!Gate::allows('donna.suscripciones.store')) {
            return $this->jsonOrAbort($request, 403, 'Sin permiso para crear suscripciones.');
        }

        $rules = [
            'client_id'    => 'required|exists:clientes,idcli',
            'plan_id'      => 'required|exists:donna_plans,id',
            'starts_at'    => 'required|date',
            'expires_at'   => 'nullable|date|after_or_equal:starts_at',
            'notes'        => 'nullable|string|max:1000',
        ];

        $plan = DonnaPlan::findOrFail($request->plan_id);

        if ($plan->service_type === 'business') {
            $rules['instance_name'] = 'required|string|max:100';
            $rules['api_base_url']  = 'required|url|max:255';
            $rules['api_key']       = 'required|string';
            $rules['phone_number']  = 'nullable|string|max:50';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $sub = DonnaSubscription::create([
                'client_id'       => $request->client_id,
                'plan_id'         => $plan->id,
                'service_type'    => $plan->service_type,
                'status'          => 'active',
                'billing_cycle'   => $plan->billing_cycle,
                'price_paid'      => $plan->price,
                'currency'        => $plan->currency,
                'starts_at'       => $request->starts_at,
                'expires_at'      => $request->expires_at,
                'last_payment_at' => now(),
                'is_enabled'      => true,
                'notes'           => $request->notes,
                'activated_by'    => Auth::user()->idemp,
            ]);

            // Crear canal WhatsApp para Donna Business
            if ($plan->service_type === 'business') {
                DonnaChannel::create([
                    'client_id'         => $request->client_id,
                    'subscription_id'   => $sub->id,
                    'service_type'      => 'business',
                    'channel_type'      => 'whatsapp',
                    'provider'          => 'evolution_api',
                    'instance_name'     => $request->instance_name,
                    'phone_number'      => $request->phone_number,
                    'api_base_url'      => rtrim($request->api_base_url, '/'),
                    'api_key_encrypted' => Crypt::encryptString($request->api_key),
                    'status'            => 'active',
                    'is_default'        => true,
                ]);
            }

            $cliente = Cliente::findOrFail($request->client_id);

            Historial::create([
                'accion'      => 'Suscripción Donna creada',
                'descripcion' => 'Cliente: ' . $cliente->nombrecli . ' | Plan: ' . $plan->name . ' | Vence: ' . ($request->expires_at ?? 'Sin vencimiento'),
                'empleado_id' => Auth::user()->idemp,
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Suscripción creada exitosamente.']);
            }
            return redirect()->route('donna.suscripciones.index')->with('success', 'Suscripción creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonOrAbort($request, 500, 'Error al crear la suscripción: ' . $e->getMessage());
        }
    }

    public function show(Request $request, string $id)
    {
        if (!Gate::allows('donna.suscripciones')) {
            return $this->jsonOrAbort($request, 403, 'Sin permiso.');
        }

        $sub = DonnaSubscription::with(['plan', 'cliente'])->findOrFail($id);

        if ($request->expectsJson()) {
            return response()->json([
                'id'          => $sub->id,
                'client_id'   => $sub->client_id,
                'plan_id'     => $sub->plan_id,
                'status'      => $sub->status,
                'starts_at'   => $sub->starts_at?->format('Y-m-d\TH:i'),
                'expires_at'  => $sub->expires_at?->format('Y-m-d\TH:i'),
                'notes'       => $sub->notes,
                'plan_name'   => $sub->plan?->name,
                'client_name' => $sub->cliente?->nombrecli,
            ]);
        }

        abort(404);
    }

    public function suspend(Request $request, string $id)
    {
        if (!Gate::allows('donna.suscripciones.store')) {
            return $this->jsonOrAbort($request, 403, 'Sin permiso.');
        }

        $request->validate(['reason' => 'nullable|string|max:500']);

        $sub = DonnaSubscription::findOrFail($id);
        $sub->update([
            'status'           => 'suspended',
            'suspended_reason' => $request->reason,
            'is_enabled'       => false,
        ]);

        Historial::create([
            'accion'      => 'Suscripción Donna suspendida',
            'descripcion' => 'Suscripción #' . $sub->id . ' | Razón: ' . ($request->reason ?? 'Sin especificar'),
            'empleado_id' => Auth::user()->idemp,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Suscripción suspendida.']);
        }
        return redirect()->route('donna.suscripciones.index')->with('success', 'Suscripción suspendida.');
    }

    public function renew(Request $request, string $id, DonnaReferralService $referrals)
    {
        if (!Gate::allows('donna.suscripciones.store')) {
            return $this->jsonOrAbort($request, 403, 'Sin permiso.');
        }

        $request->validate(['expires_at' => 'required|date|after:today']);

        $sub = DonnaSubscription::findOrFail($id);
        $sub->update([
            'status'          => 'active',
            'expires_at'      => $request->expires_at,
            'is_enabled'      => true,
            'suspended_reason'=> null,
            'last_payment_at' => now(),
        ]);

        Historial::create([
            'accion'      => 'Suscripción Donna renovada',
            'descripcion' => 'Suscripción #' . $sub->id . ' | Nueva fecha de vencimiento: ' . $request->expires_at,
            'empleado_id' => Auth::user()->idemp,
        ]);

        // Comisión de referido recurrente: cada renovación de una suscripción
        // contratada con código de referido vuelve a acreditar al partner.
        if ($sub->referral_partner_id && $sub->referralPartner) {
            $referrals->creditCommission(
                $sub->referralPartner,
                $sub,
                (float) $sub->price_paid,
                (float) $sub->referral_commission_percent,
                'renewal',
                Auth::user()->idemp
            );
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Suscripción renovada hasta ' . $request->expires_at . '.']);
        }
        return redirect()->route('donna.suscripciones.index')->with('success', 'Suscripción renovada.');
    }

    public function channel(Request $request, string $id)
    {
        if (!Gate::allows('donna.suscripciones')) {
            return $this->jsonOrAbort($request, 403, 'Sin permiso.');
        }

        $sub = DonnaSubscription::findOrFail($id);
        $channel = DonnaChannel::where('subscription_id', $sub->id)
            ->where('channel_type', 'whatsapp')
            ->first();

        return response()->json([
            'exists'        => (bool) $channel,
            'instance_name' => $channel->instance_name ?? '',
            'api_base_url'  => $channel->api_base_url ?? '',
            'phone_number'  => $channel->phone_number ?? '',
            'status'        => $channel->status ?? 'pending',
            'has_api_key'   => (bool) ($channel->api_key_encrypted ?? false),
        ]);
    }

    public function updateChannel(Request $request, string $id)
    {
        if (!Gate::allows('donna.suscripciones.store')) {
            return $this->jsonOrAbort($request, 403, 'Sin permiso para configurar el canal.');
        }

        $sub = DonnaSubscription::findOrFail($id);

        if ($sub->service_type !== 'business') {
            return $this->jsonOrAbort($request, 422, 'Solo las suscripciones Business tienen canal de WhatsApp.');
        }

        $request->validate([
            'instance_name' => 'required|string|max:100',
            'api_base_url'  => 'required|url|max:255',
            'api_key'       => 'nullable|string',
            'phone_number'  => 'nullable|string|max:50',
        ]);

        $channel = DonnaChannel::firstOrNew([
            'subscription_id' => $sub->id,
            'channel_type'    => 'whatsapp',
        ]);

        $channel->client_id     = $sub->client_id;
        $channel->service_type  = 'business';
        $channel->provider      = $channel->provider ?: 'evolution_api';
        $channel->instance_name = $request->instance_name;
        $channel->api_base_url  = rtrim($request->api_base_url, '/');
        $channel->phone_number  = $request->phone_number;
        $channel->is_default    = true;

        if ($request->filled('api_key')) {
            $channel->api_key_encrypted = Crypt::encryptString($request->api_key);
        }

        if (!$channel->api_key_encrypted) {
            return $this->jsonOrAbort($request, 422, 'Falta la API Key de Evo API.');
        }

        $channel->status = 'active';
        $channel->metadata_json = null;
        $channel->save();

        // Un mismo instance_name no puede quedar activo en más de un canal a la vez
        // (si no, /api/donna/business/resolve-webhook puede resolver al canal equivocado).
        DonnaChannel::where('instance_name', $channel->instance_name)
            ->where('channel_type', 'whatsapp')
            ->where('id', '!=', $channel->id)
            ->where('status', 'active')
            ->update(['status' => 'inactive']);

        Historial::create([
            'accion'      => 'Canal WhatsApp Donna configurado',
            'descripcion' => 'Suscripción #' . $sub->id . ' | Cliente: ' . ($sub->cliente?->nombrecli ?? $sub->client_id) . ' | Instancia: ' . $channel->instance_name,
            'empleado_id' => Auth::user()->idemp,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Canal de WhatsApp configurado correctamente.']);
        }
        return redirect()->route('donna.suscripciones.index')->with('success', 'Canal de WhatsApp configurado.');
    }

    public function destroyChannel(Request $request, string $id)
    {
        if (!Gate::allows('donna.suscripciones.store')) {
            return $this->jsonOrAbort($request, 403, 'Sin permiso para eliminar el canal.');
        }

        $sub = DonnaSubscription::findOrFail($id);

        $channel = DonnaChannel::where('subscription_id', $sub->id)
            ->where('channel_type', 'whatsapp')
            ->first();

        if (!$channel) {
            return $this->jsonOrAbort($request, 404, 'Esta suscripción no tiene un canal de WhatsApp configurado.');
        }

        $instanceName = $channel->instance_name;
        $channel->delete();

        Historial::create([
            'accion'      => 'Canal WhatsApp Donna eliminado',
            'descripcion' => 'Suscripción #' . $sub->id . ' | Cliente: ' . ($sub->cliente?->nombrecli ?? $sub->client_id) . ' | Instancia: ' . ($instanceName ?? '—'),
            'empleado_id' => Auth::user()->idemp,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Canal de WhatsApp eliminado.']);
        }
        return redirect()->route('donna.suscripciones.index')->with('success', 'Canal de WhatsApp eliminado.');
    }

    private function jsonOrAbort(Request $request, int $code, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], $code);
        }
        abort($code, $message);
    }
}
