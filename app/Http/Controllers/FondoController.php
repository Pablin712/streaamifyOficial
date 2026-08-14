<?php

namespace App\Http\Controllers;

use App\Models\Fondo;
use App\Models\Historial;
use App\Services\BancoService;
use App\Services\FondoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FondoController extends Controller
{
    public function __construct(
        protected FondoService $fondoService,
        protected BancoService $bancoService,
    ) {
    }

    // Crear un fondo nuevo (ej. futuros fondos ademas de Efectivo / Mi Negocio Efectivo)
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        Fondo::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'saldo' => 0,
            'activo' => true,
        ]);

        return redirect()->route('bancos.index')->with('success', 'Fondo "' . $request->nombre . '" creado.');
    }

    // Ajuste manual del saldo de un fondo (ej. conteo de caja, correccion)
    public function registrarTransaccion(Request $request, $fondo_id)
    {
        $request->validate([
            'monto_transaccion' => 'required|numeric|min:0.0001',
            'tipo' => 'required|in:ingreso,egreso',
            'referencia' => 'nullable|string',
        ]);

        try {
            $this->fondoService->registrarTransaccion(
                $fondo_id,
                $request->input('monto_transaccion'),
                $request->input('tipo'),
                $request->input('referencia')
            );

            return redirect()->route('bancos.index')->with('success', 'Transacción de fondo registrada correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('bancos.index')->with('error', $e->getMessage());
        }
    }

    // Recargar un fondo transfiriendo dinero desde un banco real (ej. recargar Mi Negocio Efectivo)
    public function recargar(Request $request)
    {
        $request->validate([
            'fondo_id' => 'required|exists:fondos,id',
            'banco_id' => 'required|exists:bancos,idban',
            'monto' => 'required|numeric|min:0.01',
            'referencia' => 'nullable|string',
        ]);

        try {
            $fondo = Fondo::findOrFail($request->fondo_id);
            $ref = $request->referencia ?: ('Recarga de fondo: ' . $fondo->nombre);

            $this->bancoService->registrarTransaccion($request->banco_id, $request->monto, 'egreso', $ref);
            $this->fondoService->registrarTransaccion($request->fondo_id, $request->monto, 'ingreso', $ref);

            Historial::create([
                'accion' => 'Recarga de Fondo',
                'descripcion' => 'Fondo: ' . $fondo->nombre . ' - Monto: $' . number_format($request->monto, 2),
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            return redirect()->route('bancos.index')->with('success', 'Fondo "' . $fondo->nombre . '" recargado con $' . number_format($request->monto, 2) . '.');
        } catch (\Exception $e) {
            return redirect()->route('bancos.index')->with('error', $e->getMessage());
        }
    }
}
