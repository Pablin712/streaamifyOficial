<?php

namespace App\Http\Controllers;

use App\Models\Costo;
use App\Models\Cuenta;
use App\Models\Banco;
use App\Models\Historial;
use App\Services\BancoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CostoController extends Controller
{
    protected $bancoService;

    public function __construct(BancoService $bancoService)
    {
        $this->bancoService = $bancoService;
    }

    public function index(Request $request)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();

        if (!$user->hasPermissionTo('costos')) {
            abort(403, 'No tienes permiso para ver los costos.');
        }

        // Si es petición AJAX, retornar datos paginados
        if ($request->ajax() || $request->has('ajax')) {
            return $this->getCostosAjax($request);
        }

        // Obtener todas las cuentas para el selector
        $cuentas = Cuenta::with(['valor'])
            ->where('activocue', true)
            ->orderBy('fechavencue')
            ->get();

        // Obtener todos los bancos para el selector
        $bancos = Banco::all();

        $idcueSeleccionado = $request->idcue;

        return view('finance.costos', compact('cuentas', 'bancos', 'idcueSeleccionado'));
    }

    /**
     * Obtener costos paginados para AJAX
     */
    private function getCostosAjax(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', '');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = Costo::with(['cuenta', 'transaccion']);

        // Búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('descripcioncos', 'like', "%{$search}%")
                    ->orWhere('montocos', 'like', "%{$search}%")
                    ->orWhereHas('cuenta', function ($q2) use ($search) {
                        $q2->where('idcue', 'like', "%{$search}%");
                    });
            });
        }

        // Ordenamiento
        $validSortColumns = ['idcos' => 'idcos', 'fechacos' => 'fechacos', 'montocos' => 'montocos'];
        if ($sortBy !== '' && isset($validSortColumns[$sortBy])) {
            $query->orderBy($validSortColumns[$sortBy], $sortOrder);
        } else {
            $query->orderBy('fechacos', 'desc');
        }

        $totalRecords = $query->count();
        $costos = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        $html = view('finance.partials.costos-rows', compact('costos'))->render();

        return response()->json([
            'html' => $html,
            'total_records' => $totalRecords,
            'current_page' => $page,
            'per_page' => $perPage
        ]);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();

        if (!$user->hasPermissionTo('costos.store')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para crear costos.'
                ], 403);
            }
            abort(403, 'No tienes permiso para crear costos.');
        }

        // Validar los datos - banco_id es opcional si no se pagó
        try {
            $validated = $request->validate([
                'idcue' => 'required|exists:cuentas,idcue',
                'descripcioncos' => 'required|string|max:50',
                'montocos' => 'required|numeric|min:0',
                'fechacos' => 'nullable|date',
                'banco_id' => 'nullable|exists:bancos,idban',
                'se_pago' => 'nullable|boolean'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        $sePago = $request->has('se_pago') && $request->se_pago;

        // Validar que si se pagó, debe tener banco
        if ($sePago && !$request->banco_id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar un banco si el costo se pagó.'
                ], 422);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Debe seleccionar un banco si el costo se pagó.');
        }

        // Crear el costo
        $costo = Costo::create([
            'idcue' => $request->idcue,
            'descripcioncos' => $request->descripcioncos,
            'montocos' => $request->montocos,
            'fechacos' => $request->fechacos ?? now(),
        ]);

        if ($sePago) {
            // Si se pagó, registrar transacción bancaria (egreso)
            try {
                $transaccion = $this->bancoService->registrarTransaccion(
                    $request->banco_id,
                    $request->montocos,
                    'egreso',
                    'Costo #' . $costo->idcos . ' - ' . $request->descripcioncos
                );

                // Guardar ID de transacción en el costo
                $costo->transaccion_id = $transaccion->id;
                $costo->save();

                Historial::create([
                    'accion' => 'Creación de Costo (Pagado)',
                    'descripcion' => 'Datos: ' . json_encode($costo),
                    'empleado_id' => Auth::user()->idemp,
                    'created_at' => now(),
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Costo creado y pagado correctamente.'
                    ]);
                }

                return redirect()->route('costos', ['idcue' => $request->idcue])
                    ->with('success', 'Costo creado y pagado correctamente.');

            } catch (\Exception $e) {
                // Si falla la transacción, eliminar el costo y mostrar error
                $costo->delete();

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
                    ], 500);
                }

                return redirect()->route('costos', ['idcue' => $request->idcue])
                    ->with('error', $e->getMessage());
            }
        } else {
            // Si NO se pagó, acumular en la deuda del proveedor
            $cuenta = Cuenta::with('valor.proveedor')->find($request->idcue);

            if (!$cuenta || !$cuenta->valor || !$cuenta->valor->proveedor) {
                $costo->delete();

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pudo determinar el proveedor de esta cuenta.'
                    ], 500);
                }

                return redirect()->route('costos', ['idcue' => $request->idcue])
                    ->with('error', 'No se pudo determinar el proveedor de esta cuenta.');
            }

            $proveedor = $cuenta->valor->proveedor;

            // Buscar o crear deuda del proveedor
            $deuda = \App\Models\Deuda::firstOrCreate(
                ['proveedor_id' => $proveedor->idpro, 'estado' => 'pendiente'],
                ['monto' => 0, 'monto_pagado' => 0]
            );

            // Acumular el monto al total de la deuda
            $deuda->monto += $request->montocos;
            $deuda->save();

            Historial::create([
                'accion' => 'Creación de Costo (Deuda)',
                'descripcion' => 'Costo #' . $costo->idcos . ' - Proveedor: ' . $proveedor->nombrepro . ' - Monto acumulado: $' . $deuda->monto,
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Costo registrado. Deuda acumulada con ' . $proveedor->nombrepro . ': $' . number_format($deuda->monto_restante, 2)
                ]);
            }

            return redirect()->route('costos', ['idcue' => $request->idcue])
                ->with('success', 'Costo registrado. Deuda acumulada con ' . $proveedor->nombrepro . ': $' . number_format($deuda->monto_restante, 2));
        }
    }

    public function update(Request $request, $idcos)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        if (!$user->hasPermissionTo('costos.update')) {
            abort(403, 'No tienes permiso para actualizar costos.');
        }

        $request->validate([
            'descripcioncos' => 'required|string|max:50',
            'montocos' => 'required|numeric',
            'fechacos' => 'required|date',
            'banco_id' => 'required|exists:bancos,idban',
        ]);

        $costo = Costo::findOrFail($idcos);

        $montoAnterior = $costo->montocos;
        // Obtener banco anterior desde la transacción
        $bancoAnterior = $costo->transaccion ? $costo->transaccion->banco_id : null;
        $transaccionAnterior = $costo->transaccion_id;

        Historial::create([
            'accion' => 'Actualización de Costo',
            'descripcion' => 'Datos antiguos: ' . json_encode($costo),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $costo->update([
            'descripcioncos' => $request->descripcioncos,
            'montocos' => $request->montocos,
            'fechacos' => $request->fechacos,
        ]);

        // Si el banco cambió o el monto cambió, ajustar transacciones
        if ($bancoAnterior && ($bancoAnterior != $request->banco_id || $montoAnterior != $request->montocos)) {
            // Anular transacción anterior
            if ($transaccionAnterior) {
                $this->bancoService->anularTransaccion($transaccionAnterior);
            }
        }

        // Registrar nueva transacción
        if ($request->banco_id) {
            try {
                $transaccion = $this->bancoService->registrarTransaccion(
                    $request->banco_id,
                    $request->montocos,
                    'egreso',
                    'Costo #' . $costo->idcos . ' - ' . $request->descripcioncos
                );

                $costo->transaccion_id = $transaccion->id;
                $costo->save();
            } catch (\Exception $e) {
                return redirect()->route('costos')->with('error', $e->getMessage());
            }
        }

        return redirect()->route('costos')->with('success', 'Costo actualizado con éxito.');
    }

    public function destroy($idcos)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        if (!$user->hasPermissionTo('costos.destroy')) {
            abort(403, 'No tienes permiso para eliminar costos.');
        }

        $costo = Costo::findOrFail($idcos);
        $idcue = $costo->idcue; // Para regresar a la cuenta seleccionada

        // Anular transacción si existe
        if ($costo->transaccion_id) {
            $this->bancoService->anularTransaccion($costo->transaccion_id);
        }

        Historial::create([
            'accion' => 'Eliminación de Costo',
            'descripcion' => 'Datos Eliminados: ' . json_encode($costo),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $costo->delete();

        return redirect()->route('costos', ['idcue' => $idcue])
            ->with('success', 'Costo eliminado correctamente.');
    }
}
