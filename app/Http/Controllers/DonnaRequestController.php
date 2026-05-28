<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DonnaPlan;
use App\Models\DonnaRequest;
use App\Models\DonnaSubscription;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class DonnaRequestController extends Controller
{
    public function index()
    {
        if (!Gate::allows('donna.solicitudes')) {
            abort(403);
        }

        $solicitudes = DonnaRequest::with(['plan', 'cliente', 'reviewedBy'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('created_at')
            ->get();

        return view('donna.solicitudes.index', compact('solicitudes'));
    }

    public function approve(Request $request, string $id)
    {
        if (!Gate::allows('donna.solicitudes.manage')) {
            return $this->jsonOrAbort($request, 403, 'Sin permiso para aprobar solicitudes.');
        }

        $request->validate([
            'employee_notes' => 'nullable|string|max:1000',
            'expires_at'     => 'nullable|date|after:today',
        ]);

        $solicitud = DonnaRequest::where('status', 'pending')->findOrFail($id);
        $plan = DonnaPlan::findOrFail($solicitud->plan_id);

        DB::beginTransaction();
        try {
            $now = now();
            $expiresAt = $request->expires_at ?? match($plan->billing_cycle) {
                'monthly'  => $now->copy()->addMonth()->toDateTimeString(),
                'yearly'   => $now->copy()->addYear()->toDateTimeString(),
                default    => null,
            };

            DonnaSubscription::create([
                'client_id'       => $solicitud->client_id,
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
                'notes'           => $request->employee_notes,
                'activated_by'    => Auth::user()->idemp,
            ]);

            $solicitud->update([
                'status'         => 'approved',
                'employee_notes' => $request->employee_notes,
                'reviewed_by'    => Auth::user()->idemp,
                'reviewed_at'    => $now,
            ]);

            Historial::create([
                'accion'      => 'Solicitud Donna aprobada',
                'descripcion' => 'Solicitud #' . $solicitud->id . ' | Cliente ID: ' . $solicitud->client_id . ' | Plan: ' . $plan->name,
                'empleado_id' => Auth::user()->idemp,
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Solicitud aprobada. Suscripción activada.']);
            }
            return redirect()->route('donna.solicitudes.index')->with('success', 'Solicitud aprobada y suscripción activada.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonOrAbort($request, 500, 'Error al aprobar: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, string $id)
    {
        if (!Gate::allows('donna.solicitudes.manage')) {
            return $this->jsonOrAbort($request, 403, 'Sin permiso para rechazar solicitudes.');
        }

        $request->validate(['employee_notes' => 'nullable|string|max:1000']);

        $solicitud = DonnaRequest::where('status', 'pending')->findOrFail($id);

        $solicitud->update([
            'status'         => 'rejected',
            'employee_notes' => $request->employee_notes,
            'reviewed_by'    => Auth::user()->idemp,
            'reviewed_at'    => now(),
        ]);

        Historial::create([
            'accion'      => 'Solicitud Donna rechazada',
            'descripcion' => 'Solicitud #' . $solicitud->id . ' | Cliente ID: ' . $solicitud->client_id,
            'empleado_id' => Auth::user()->idemp,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Solicitud rechazada.']);
        }
        return redirect()->route('donna.solicitudes.index')->with('success', 'Solicitud rechazada.');
    }

    private function jsonOrAbort(Request $request, int $code, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], $code);
        }
        abort($code, $message);
    }
}
