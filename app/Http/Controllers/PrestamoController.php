<?php

namespace App\Http\Controllers;

use App\Models\Deudor;
use App\Models\Historial;
use App\Models\Prestamo;
use App\Services\PrestamoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrestamoController extends Controller
{
    public function __construct(protected PrestamoService $prestamoService)
    {
    }

    // Otorgar un nuevo prestamo. Crea el deudor si no existe (deudor_id vacio + deudor_nombre presente).
    public function store(Request $request)
    {
        $request->validate([
            'deudor_id' => 'nullable|exists:deudores,id',
            'deudor_nombre' => 'required_without:deudor_id|nullable|string|max:150',
            'deudor_telefono' => 'nullable|string|max:20',
            'monto' => 'required|numeric|min:0.01',
            'banco_id' => 'nullable|required_without:fondo_id|exists:bancos,idban',
            'fondo_id' => 'nullable|required_without:banco_id|exists:fondos,id',
            'motivo' => 'nullable|string|max:255',
        ]);

        try {
            $deudorId = $request->deudor_id;
            if (!$deudorId) {
                $deudor = Deudor::create([
                    'nombre' => $request->deudor_nombre,
                    'telefono' => $request->deudor_telefono,
                ]);
                $deudorId = $deudor->id;
            }

            $prestamo = $this->prestamoService->otorgar([
                'deudor_id' => $deudorId,
                'monto' => $request->monto,
                'banco_id' => $request->banco_id,
                'fondo_id' => $request->fondo_id,
                'motivo' => $request->motivo,
            ]);

            Historial::create([
                'accion' => 'Prestamo Otorgado',
                'descripcion' => 'Deudor: ' . $prestamo->deudor->nombre . ' - Monto: $' . number_format($request->monto, 2),
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            return redirect()->route('bancos.index')->with('success', 'Préstamo registrado a ' . $prestamo->deudor->nombre . '.');
        } catch (\Exception $e) {
            return redirect()->route('bancos.index')->with('error', $e->getMessage());
        }
    }

    // Registrar abono (parcial o total) a un prestamo
    public function abonar(Request $request, $id)
    {
        $request->validate([
            'monto_abono' => 'required|numeric|min:0.01',
            'banco_id' => 'nullable|required_without:fondo_id|exists:bancos,idban',
            'fondo_id' => 'nullable|required_without:banco_id|exists:fondos,id',
        ]);

        try {
            $prestamo = $this->prestamoService->abonar($id, (float) $request->monto_abono, $request->banco_id, $request->fondo_id);

            $mensaje = $prestamo->estado === 'pagado'
                ? 'Préstamo con ' . $prestamo->deudor->nombre . ' pagado completamente.'
                : 'Abono registrado. Restante: $' . number_format($prestamo->monto_restante, 2);

            Historial::create([
                'accion' => 'Abono de Prestamo',
                'descripcion' => 'Deudor: ' . $prestamo->deudor->nombre . ' - Abono: $' . number_format($request->monto_abono, 2) . ' - Total pagado: $' . number_format($prestamo->monto_pagado, 2) . ' de $' . number_format($prestamo->monto, 2),
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            return redirect()->route('bancos.index')->with('success', $mensaje);
        } catch (\Exception $e) {
            return redirect()->route('bancos.index')->with('error', $e->getMessage());
        }
    }

    // Registrar abono contra la deuda TOTAL de un deudor (todos sus prestamos pendientes)
    public function abonarDeudor(Request $request, $deudorId)
    {
        $request->validate([
            'monto_abono' => 'required|numeric|min:0.01',
            'banco_id' => 'nullable|required_without:fondo_id|exists:bancos,idban',
            'fondo_id' => 'nullable|required_without:banco_id|exists:fondos,id',
        ]);

        try {
            $resultado = $this->prestamoService->abonarDeudor($deudorId, (float) $request->monto_abono, $request->banco_id, $request->fondo_id);

            $mensaje = $resultado['restante'] <= 0
                ? 'Deuda de ' . $resultado['deudor']->nombre . ' saldada completamente.'
                : 'Abono registrado. Restante: $' . number_format($resultado['restante'], 2);

            Historial::create([
                'accion' => 'Abono de Prestamo',
                'descripcion' => 'Deudor: ' . $resultado['deudor']->nombre . ' - Abono a deuda total: $' . number_format($resultado['monto_abonado'], 2) . ' - Restante: $' . number_format($resultado['restante'], 2),
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            return redirect()->route('bancos.index')->with('success', $mensaje);
        } catch (\Exception $e) {
            return redirect()->route('bancos.index')->with('error', $e->getMessage());
        }
    }
}
