<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DonnaIntegration;
use App\Models\DonnaPlan;
use App\Models\DonnaSubscription;
use App\Models\DonnaRequest;
use App\Models\Cliente;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;
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

        $request->validate([
            'client_id'  => 'required|exists:clientes,idcli',
            'plan_id'    => 'required|exists:donna_plans,id',
            'starts_at'  => 'required|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $plan = DonnaPlan::findOrFail($request->plan_id);

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

    public function renew(Request $request, string $id)
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

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Suscripción renovada hasta ' . $request->expires_at . '.']);
        }
        return redirect()->route('donna.suscripciones.index')->with('success', 'Suscripción renovada.');
    }

    private function jsonOrAbort(Request $request, int $code, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], $code);
        }
        abort($code, $message);
    }
}
