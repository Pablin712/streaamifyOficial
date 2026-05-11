<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use App\Models\Gasto;
use App\Models\TipoGasto;
use App\Services\BancoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class BancoController extends Controller
{
    protected $bancoService;

    public function __construct(BancoService $bancoService)
    {
        $this->bancoService = $bancoService;
    }

    /**
     * Convierte distintos formatos legacy de ruta a una ruta de disco public válida.
     */
    private function resolvePublicDiskPath(?string $fotoPath): ?string
    {
        if (empty($fotoPath)) {
            return null;
        }

        $path = trim($fotoPath);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return null;
        }

        if (str_starts_with($path, 'storage/')) {
            return substr($path, strlen('storage/'));
        }

        if (str_starts_with($path, 'fotos/')) {
            return $path;
        }

        return 'fotos/' . ltrim($path, '/');
    }

    /**
     * Entrega imagen de banco desde disco public.
     * Evita depender del acceso directo a /storage en hostings con restricciones.
     */
    public function foto(string $path)
    {
        $diskPath = $this->resolvePublicDiskPath($path);

        if (!$diskPath || !Storage::disk('public')->exists($diskPath)) {
            $fallback = public_path('images/Icono.png');
            if (is_file($fallback)) {
                return response()->file($fallback, [
                    'Cache-Control' => 'public, max-age=3600',
                ]);
            }

            abort(404);
        }

        return response()->file(Storage::disk('public')->path($diskPath), [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    // Listar bancos
    public function index(Request $request)
    {
        // Si es petición AJAX para transacciones, devolver JSON
        if ($request->ajax() || $request->has('ajax')) {
            return $this->loadTransactions($request);
        }

        $bancos = Banco::orderBy('monto', 'desc')->get();

        // Obtener deudas pendientes con información del proveedor
        $deudas = \App\Models\Deuda::with(['proveedor'])
            ->where('estado', 'pendiente')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Calcular totales financieros
        $totalDisponible = $bancos->sum('monto'); // Total de dinero en todos los bancos
        $totalDeudasMonto = $deudas->sum('monto'); // Total acumulado de deudas
        $totalDeudasPagado = $deudas->sum('monto_pagado'); // Total pagado de deudas
        $totalDeudasPendientes = $totalDeudasMonto - $totalDeudasPagado; // Deuda restante

        // Para la vista inicial, no cargar todas las transacciones
        // La tabla usará modo servidor y las cargará vía AJAX
        return view('finance.bancos.index', compact('bancos', 'deudas', 'totalDisponible', 'totalDeudasPendientes'));
    }

    // Cargar transacciones con paginación (AJAX)
    private function loadTransactions(Request $request)
    {
        $perPage = $request->input('per_page', 25);
        $page = $request->input('page', 1);
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', '');
        $sortOrder = $request->input('sort_order', 'desc');

        // Query base
        $query = \App\Models\Transaccion::with('banco')
            ->where('anulada', false);

        // Búsqueda
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('referencia', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhere('monto_transaccion', 'like', "%{$search}%")
                  ->orWhereHas('banco', function($subQ) use ($search) {
                      $subQ->where('nombreban', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenamiento
        $validSortColumns = ['id' => 'id', 'created_at' => 'created_at', 'fecha' => 'fecha'];
        if ($sortBy !== '' && isset($validSortColumns[$sortBy])) {
            $query->orderBy($validSortColumns[$sortBy], $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Paginación
        $totalRecords = $query->count();
        $transacciones = $query
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Generar HTML usando vista parcial
        $html = view('finance.bancos.partials.transacciones-rows', compact('transacciones'))->render();

        return response()->json([
            'html' => $html,
            'total_records' => $totalRecords,
            'current_page' => $page,
            'per_page' => $perPage
        ]);
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

    public function transferirFondos(Request $request)
    {
        $request->validate([
            'banco_origen_id' => 'required|exists:bancos,idban',
            'banco_destino_id' => 'required|exists:bancos,idban|different:banco_origen_id',
            'monto_transferir' => 'required|numeric|min:0.01',
            'fees' => 'nullable|numeric|min:0',
            'fees_bank' => 'required|in:origen,destino',
        ]);

        try {
            // Transferencia de fondos del origen al destino
            $this->bancoService->registrarTransaccion(
                $request->banco_origen_id,
                $request->monto_transferir,
                'egreso',
                'Transferencia a banco ID ' . $request->banco_destino_id
            );
            $this->bancoService->registrarTransaccion(
                $request->banco_destino_id,
                $request->monto_transferir,
                'ingreso',
                'Transferencia desde banco ID ' . $request->banco_origen_id
            );

            // Registrar fees si existe
            $fees = $request->fees ?? 0;
            if ($fees > 0) {
                $transaccionFees = $this->bancoService->registrarTransaccion(
                    $request->fees_bank === 'origen' ? $request->banco_origen_id : $request->banco_destino_id,
                    $fees,
                    'egreso',
                    'Fees de transferencia'
                );

                $tipoGastoOtro = TipoGasto::firstOrCreate([
                    'detalletip' => 'OTRO',
                ]);

                Gasto::create([
                    'idtip' => $tipoGastoOtro->idtip,
                    'fechagas' => now()->toDateString(),
                    'montogas' => $fees,
                    'descripciongas' => 'Fees - Transferencia entre bancos',
                    'transaccion_id' => $transaccionFees->id,
                ]);
            }

            $mensaje = 'Fondos transferidos correctamente.';
            if ($fees > 0) {
                $mensaje .= ' Se aplicaron fees de $' . number_format($fees, 2) . ' registrados como gasto.';
            }

            return redirect()->route('bancos.index')->with('success', $mensaje);
        } catch (\Exception $e) {
            return redirect()->route('bancos.index')->with('error', $e->getMessage());
        }
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
            // Guardar formato canónico relativo al disco public.
            $data['foto'] = 'fotos/' . $nombreFoto;
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
            // Eliminar foto anterior si existe (soporta formatos legacy).
            $oldDiskPath = $this->resolvePublicDiskPath($banco->foto);
            if ($oldDiskPath && Storage::disk('public')->exists($oldDiskPath)) {
                Storage::disk('public')->delete($oldDiskPath);
            }

            $foto = $request->file('foto');
            $nombreFoto = time() . '_' . $foto->getClientOriginalName();
            $foto->storeAs('fotos', $nombreFoto, 'public');
            // Guardar formato canónico relativo al disco public.
            $data['foto'] = 'fotos/' . $nombreFoto;
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
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            return redirect()->route('bancos.index')->with('success', $mensaje);
        } catch (\Exception $e) {
            return redirect()->route('bancos.index')->with('error', $e->getMessage());
        }
    }
}
