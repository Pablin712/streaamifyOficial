<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use App\Services\BancoService;
use Illuminate\Http\Request;

class BancoController extends Controller
{
    protected $bancoService;

    public function __construct(BancoService $bancoService)
    {
        $this->bancoService = $bancoService;
    }

    // Listar bancos
    public function index()
    {
        $bancos = Banco::with(['transacciones' => function ($query) {
            $query->where('anulada', false)
                ->orderBy('created_at', 'desc');
        }])->get();

        // Obtener deudas pendientes con información del proveedor
        $deudas = \App\Models\Deuda::with(['proveedor'])
            ->where('estado', 'pendiente')
            ->orderBy('updated_at', 'desc')
            ->get();

        $allTransactions = collect();
        foreach ($bancos as $banco) {
            $allTransactions = $allTransactions->concat($banco->transacciones);
        }

        // Calcular totales financieros
        $totalDisponible = $bancos->sum('monto'); // Total de dinero en todos los bancos
        $totalDeudasMonto = $deudas->sum('monto'); // Total acumulado de deudas
        $totalDeudasPagado = $deudas->sum('monto_pagado'); // Total pagado de deudas
        $totalDeudasPendientes = $totalDeudasMonto - $totalDeudasPagado; // Deuda restante

        return view('finance.bancos.index', compact('bancos', 'deudas', 'allTransactions', 'totalDisponible', 'totalDeudasPendientes'));
    }

    // Registrar una transacción
    public function registrarTransaccion(Request $request, $banco_id)
    {
        $request->validate([
            'monto_transaccion' => 'required|numeric',
            'tipo' => 'required|in:ingreso,egreso',
            'referencia' => 'nullable|string',
        ]);

        $transaccion = $this->bancoService->registrarTransaccion(
            $banco_id,
            $request->input('monto_transaccion'),
            $request->input('tipo'),
            $request->input('referencia')
        );

        return redirect()->route('bancos.index')->with('success', 'Transacción registrada correctamente.');
    }

    // Crear nuevo banco (solo Admin)
    public function store(Request $request)
    {
        $request->validate([
            'nombreban' => 'required|string|max:255',
            'propietarioban' => 'required|string|max:255',
            'cedulaban' => 'required|string|max:20',
            'numeroban' => 'required|string|max:50',
            'tipoban' => 'required|in:Ahorros,Corriente,Efectivo',
            'detalleban' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'nombreban' => $request->nombreban,
            'propietarioban' => $request->propietarioban,
            'cedulaban' => $request->cedulaban,
            'numeroban' => $request->numeroban,
            'tipoban' => $request->tipoban,
            'detalleban' => $request->detalleban,
            'monto' => 0, // Siempre inicia en 0
        ];

        // Manejar foto si se subió
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nombreFoto = time() . '_' . $foto->getClientOriginalName();
            $foto->storeAs('fotos', $nombreFoto, 'public');
            $data['foto'] = 'storage/fotos/' . $nombreFoto;
        }

        Banco::create($data);

        return redirect()->route('bancos.index')->with('success', 'Banco creado correctamente.');
    }

    // Actualizar banco (solo nombre, detalleban, tipoban y foto - NO monto)
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombreban' => 'required|string|max:255',
            'tipoban' => 'required|in:Ahorros,Corriente,Efectivo',
            'detalleban' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $banco = Banco::findOrFail($id);

        $data = [
            'nombreban' => $request->nombreban,
            'tipoban' => $request->tipoban,
            'detalleban' => $request->detalleban,
        ];

        // Manejar foto si se subió
        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existe (storage/fotos/...)
            if ($banco->foto && \Storage::disk('public')->exists(str_replace('storage/', '', $banco->foto))) {
                \Storage::disk('public')->delete(str_replace('storage/', '', $banco->foto));
            }

            $foto = $request->file('foto');
            $nombreFoto = time() . '_' . $foto->getClientOriginalName();
            $foto->storeAs('fotos', $nombreFoto, 'public');
            $data['foto'] = 'storage/fotos/' . $nombreFoto;
        }

        $banco->update($data);

        return redirect()->route('bancos.index')->with('success', 'Banco actualizado correctamente.');
    }

    // Pagar deuda (puede ser abono parcial o total)
    public function pagarDeuda(Request $request, $id)
    {
        $request->validate([
            'monto_abono' => 'required|numeric|min:0.01',
            'banco_id' => 'required|exists:bancos,idban',
        ]);

        $deuda = \App\Models\Deuda::with('proveedor')->findOrFail($id);
        $montoRestante = $deuda->monto_restante;

        // Validar que el monto no exceda la deuda restante
        if ($request->monto_abono > $montoRestante) {
            return redirect()->route('bancos.index')
                ->with('error', 'El monto a pagar ($' . number_format($request->monto_abono, 2) . ') no puede ser mayor a la deuda restante ($' . number_format($montoRestante, 2) . ').');
        }

        try {
            // Registrar transacción bancaria (egreso)
            $transaccion = $this->bancoService->registrarTransaccion(
                $request->banco_id,
                $request->monto_abono,
                'egreso',
                'Abono a ' . $deuda->proveedor->nombrepro . ' - Deuda #' . $deuda->id
            );

            // Incrementar monto pagado
            $deuda->monto_pagado += $request->monto_abono;

            // Si la deuda se pagó completamente, cambiar estado
            if ($deuda->monto_pagado >= $deuda->monto) {
                $deuda->estado = 'pagada';
                $mensaje = 'Deuda con ' . $deuda->proveedor->nombrepro . ' pagada completamente.';
            } else {
                $montoRestanteNuevo = $deuda->monto - $deuda->monto_pagado;
                $mensaje = 'Abono realizado a ' . $deuda->proveedor->nombrepro . '. Monto restante: $' . number_format($montoRestanteNuevo, 2);
            }

            $deuda->save();

            // Registrar historial
            \App\Models\Historial::create([
                'accion' => 'Pago de Deuda',
                'descripcion' => 'Proveedor: ' . $deuda->proveedor->nombrepro . ' - Abono: $' . $request->monto_abono . ' - Total pagado: $' . $deuda->monto_pagado . ' de $' . $deuda->monto,
                'empleado_id' => \Auth::user()->idemp,
                'created_at' => now(),
            ]);

            return redirect()->route('bancos.index')->with('success', $mensaje);
        } catch (\Exception $e) {
            return redirect()->route('bancos.index')->with('error', $e->getMessage());
        }
    }
}
