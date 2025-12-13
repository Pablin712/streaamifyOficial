<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Models\TipoGasto;
use App\Models\Banco;
use App\Models\Historial;
use App\Services\BancoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GastoController extends Controller
{
    protected $bancoService;

    public function __construct(BancoService $bancoService)
    {
        $this->bancoService = $bancoService;
    }

    // Mostrar todos los gastos
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('gastos')) {
            abort(403, 'No tienes permiso para ver los gastos.');
        }

        $gastos = Gasto::with(['tipoGasto', 'transaccion'])->orderBy('fechagas', 'desc')->get();
        $tipoGastos = TipoGasto::all();
        $bancos = Banco::all();

        return view('finance.gastos', compact('gastos', 'tipoGastos', 'bancos'));
    }

    // Crear un nuevo gasto desde el modal
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('gastos.store')) {
            abort(403, 'No tienes permiso para crear costos.');
        }

        $request->validate([
            'idtip' => 'required|exists:tipo_gasto,idtip',
            'fechagas' => 'required|date',
            'montogas' => 'required|numeric',
            'descripciongas' => 'required|string|max:50',
            'banco_id' => 'required|exists:bancos,idban',
        ]);

        $gasto = Gasto::create([
            'idtip' => $request->idtip,
            'fechagas' => $request->fechagas,
            'montogas' => $request->montogas,
            'descripciongas' => $request->descripciongas,
            'banco_id' => $request->banco_id,
        ]);

        // Registrar transacción bancaria (egreso)
        if ($request->banco_id) {
            try {
                $transaccion = $this->bancoService->registrarTransaccion(
                    $request->banco_id,
                    $request->montogas,
                    'egreso',
                    'Gasto #' . $gasto->idgas . ' - ' . $request->descripciongas
                );

                // Guardar ID de transacción en el gasto
                $gasto->transaccion_id = $transaccion->id;
                $gasto->save();
            } catch (\Exception $e) {
                // Si falla la transacción, eliminar el gasto y mostrar error
                $gasto->delete();
                return redirect()->route('gastos')->with('error', $e->getMessage());
            }
        }

        Historial::create([
            'accion' => 'Creación de Gasto',
            'descripcion' => 'Datos: ' . json_encode($gasto),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        return redirect()->route('gastos')->with('success', 'Gasto creado con éxito');
    }

    // Mostrar el formulario para editar un gasto (modal)
    public function edit($id)
    {
        // Para la edición no se aplica verificación de permisos manualmente, pero puedes agregarla si lo deseas:
        if (!Auth::user()->hasPermissionTo('gastos.update')) {
            abort(403, 'No tienes permiso para editar costos.');
        }

        $gasto = Gasto::findOrFail($id);
        $tipoGastos = TipoGasto::all();
        $bancos = Banco::all();

        return response()->json([
            'gasto' => $gasto,
            'tipoGastos' => $tipoGastos,
            'bancos' => $bancos
        ]);
    }

    // Actualizar un gasto (desde el modal)
    public function update(Request $request, $idgas)
    {
        if (!Auth::user()->hasPermissionTo('gastos.update')) {
            abort(403, 'No tienes permiso para actualizar costos.');
        }

        $request->validate([
            'idtip' => 'required|exists:tipo_gasto,idtip',
            'fechagas' => 'required|date',
            'montogas' => 'required|numeric',
            'descripciongas' => 'required|string|max:50',
            'banco_id' => 'required|exists:bancos,idban',
        ]);

        $gasto = Gasto::findOrFail($idgas);

        $montoAnterior = $gasto->montogas;
        // Obtener banco anterior desde la transacción
        $bancoAnterior = $gasto->transaccion ? $gasto->transaccion->banco_id : null;
        $transaccionAnterior = $gasto->transaccion_id;

        Historial::create([
            'accion' => 'Actualización de Gasto',
            'descripcion' => 'Datos antiguos: ' . json_encode($gasto),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $gasto->update([
            'idtip' => $request->idtip,
            'fechagas' => $request->fechagas,
            'montogas' => $request->montogas,
            'descripciongas' => $request->descripciongas,
        ]);

        // Si el banco cambió o el monto cambió, ajustar transacciones
        if ($bancoAnterior && ($bancoAnterior != $request->banco_id || $montoAnterior != $request->montogas)) {
            // Anular transacción anterior
            if ($transaccionAnterior) {
                $this->bancoService->anularTransaccion($transaccionAnterior);
            }
        }

        // Registrar nueva transacción si hay banco seleccionado
        if ($request->banco_id) {
            try {
                $transaccion = $this->bancoService->registrarTransaccion(
                    $request->banco_id,
                    $request->montogas,
                    'egreso',
                    'Gasto #' . $gasto->idgas . ' - ' . $request->descripciongas
                );

                $gasto->transaccion_id = $transaccion->id;
                $gasto->save();
            } catch (\Exception $e) {
                return redirect()->route('gastos')->with('error', $e->getMessage());
            }
        }

        return redirect()->route('gastos')->with('success', 'Gasto actualizado con éxito');
    }

    // Eliminar un gasto
    public function destroy($id)
    {
        if (!Auth::user()->hasPermissionTo('gastos.destroy')) {
            abort(403, 'No tienes permiso para eliminar costos.');
        }

        $gasto = Gasto::findOrFail($id);

        // Anular transacción si existe
        if ($gasto->transaccion_id) {
            $this->bancoService->anularTransaccion($gasto->transaccion_id);
        }

        Historial::create([
            'accion' => 'Eliminación de Gasto',
            'descripcion' => 'Datos Eliminados: ' . json_encode($gasto),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $gasto->delete();

        return redirect()->route('gastos')->with('success', 'Gasto eliminado con éxito');
    }
}
