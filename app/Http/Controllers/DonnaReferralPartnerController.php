<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DonnaReferralEarning;
use App\Models\DonnaReferralPartner;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DonnaReferralPartnerController extends Controller
{
    public function index()
    {
        if (!Gate::allows('donna.referidos')) {
            abort(403, 'No tienes permiso para ver los referidos de Donna.');
        }

        $partners = DonnaReferralPartner::with('cliente')
            ->withCount('earnings')
            ->withSum('earnings', 'commission_amount')
            ->orderByDesc('created_at')
            ->get();

        $earnings = DonnaReferralEarning::with(['partner.cliente', 'cliente'])
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $clientes = Cliente::orderBy('nombrecli')->get();

        return view('donna.referidos.index', compact('partners', 'earnings', 'clientes'));
    }

    public function store(Request $request)
    {
        if (!Gate::allows('donna.referidos.store')) {
            return $this->jsonOrAbort($request, 403, 'No tienes permiso para crear partners de referido.');
        }

        $request->validate([
            'client_id'         => 'required|exists:clientes,idcli',
            'code'              => 'required|string|max:30|unique:donna_referral_partners,code',
            'discount_amount'   => 'required|numeric|min:0',
            'commission_percent' => 'required|numeric|min:0.01|max:100',
            'is_active'         => 'required|boolean',
            'notes'             => 'nullable|string|max:1000',
        ]);

        $partner = DonnaReferralPartner::create([
            'client_id'         => $request->client_id,
            'code'              => strtoupper($request->code),
            'discount_amount'   => $request->discount_amount,
            'commission_percent' => $request->commission_percent,
            'is_active'         => $request->is_active,
            'notes'             => $request->notes,
        ]);

        $cliente = Cliente::findOrFail($request->client_id);

        Historial::create([
            'accion'      => 'Partner de referido Donna creado',
            'descripcion' => 'Cliente: ' . $cliente->nombrecli . ' | Código: ' . $partner->code
                . ' | Descuento: $' . number_format($partner->discount_amount, 2)
                . ' | Comisión: ' . $partner->commission_percent . '%',
            'empleado_id' => Auth::user()->idemp,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Partner de referido creado.', 'partner' => $partner]);
        }
        return redirect()->route('donna.referidos.index')->with('success', 'Partner de referido creado.');
    }

    public function show(Request $request, string $id)
    {
        if (!Gate::allows('donna.referidos')) {
            return $this->jsonOrAbort($request, 403, 'Sin permiso.');
        }

        $partner = DonnaReferralPartner::with('cliente')->findOrFail($id);

        if ($request->expectsJson()) {
            return response()->json([
                'id'                => $partner->id,
                'client_id'         => $partner->client_id,
                'client_name'       => $partner->cliente?->nombrecli,
                'code'              => $partner->code,
                'discount_amount'    => (float) $partner->discount_amount,
                'commission_percent' => (float) $partner->commission_percent,
                'is_active'         => $partner->is_active,
                'notes'             => $partner->notes,
            ]);
        }

        abort(404);
    }

    public function update(Request $request, string $id)
    {
        if (!Gate::allows('donna.referidos.store')) {
            return $this->jsonOrAbort($request, 403, 'No tienes permiso para editar partners de referido.');
        }

        $partner = DonnaReferralPartner::findOrFail($id);

        $request->validate([
            'client_id'         => 'required|exists:clientes,idcli',
            'code'              => 'required|string|max:30|unique:donna_referral_partners,code,' . $id,
            'discount_amount'   => 'required|numeric|min:0',
            'commission_percent' => 'required|numeric|min:0.01|max:100',
            'is_active'         => 'required|boolean',
            'notes'             => 'nullable|string|max:1000',
        ]);

        $partner->update([
            'client_id'         => $request->client_id,
            'code'              => strtoupper($request->code),
            'discount_amount'   => $request->discount_amount,
            'commission_percent' => $request->commission_percent,
            'is_active'         => $request->is_active,
            'notes'             => $request->notes,
        ]);

        Historial::create([
            'accion'      => 'Partner de referido Donna editado',
            'descripcion' => 'Partner #' . $partner->id . ' | Código: ' . $partner->code
                . ' | Descuento: $' . number_format($partner->discount_amount, 2)
                . ' | Comisión: ' . $partner->commission_percent . '%',
            'empleado_id' => Auth::user()->idemp,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Partner de referido actualizado.', 'partner' => $partner]);
        }
        return redirect()->route('donna.referidos.index')->with('success', 'Partner de referido actualizado.');
    }

    public function destroy(Request $request, string $id)
    {
        if (!Gate::allows('donna.referidos.destroy')) {
            return $this->jsonOrAbort($request, 403, 'No tienes permiso para eliminar partners de referido.');
        }

        $partner = DonnaReferralPartner::findOrFail($id);
        $nombre = $partner->code;
        $partner->delete();

        Historial::create([
            'accion'      => 'Partner de referido Donna eliminado',
            'descripcion' => 'Código eliminado: ' . $nombre,
            'empleado_id' => Auth::user()->idemp,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Partner de referido eliminado.']);
        }
        return redirect()->route('donna.referidos.index')->with('success', 'Partner de referido eliminado.');
    }

    private function jsonOrAbort(Request $request, int $code, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], $code);
        }
        abort($code, $message);
    }
}
