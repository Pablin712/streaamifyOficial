<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banco;
use App\Models\Recarga;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class RecargaController extends Controller
{
    /*
    public function __construct() {
        $this->middleware('can:empleado.recargas.index')->only('index');
        $this->middleware('can:empleado.recargas.updateEstado')->only('updateEstado');
    }
    */
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('empleado.recargas.index')) {
            abort(403, 'No tienes permiso para ver las recargas.');
        }
        // Obtener las recargas con relaciones (cliente, estado y banco)
        $recargas = Recarga::with(['cliente', 'estado', 'banco'])->orderBy('created_at', 'desc')->get();

        return view('sales.recargas.index', compact('recargas'));
    }

    public function updateEstado(Request $request, $idrec)
    {
        if (!Auth::user()->hasPermissionTo('empleado.recargas.updateEstado')) {
            abort(403, 'No tienes permiso para aprobar o rechazar las recargas.');
        }
        try {
            DB::beginTransaction(); // Iniciar transacción
            $recarga = Recarga::where('idrec', $idrec)
                ->where('idestado', 1) // Solo procesar si sigue pendiente
                ->lockForUpdate() // Bloquea la fila hasta que termine la transacción
                ->first();
            if (!$recarga) {
                return redirect()->route('empleado.recargas.index')
                    ->withErrors(['error' => 'La recarga ya fue procesada por otro usuario.']);
            }
            // Mapeo de texto a IDs
            $estadoMap = [
                'aprobado' => 3,
                'rechazado' => 2,
            ];
            // Validar que el estado sea "aprobado" o "rechazado"
            $request->validate([
                'idestado' => 'required|in:aprobado,rechazado',
            ]);
            // Obtener el ID del estado
            $estadoId = $estadoMap[$request->idestado];
            // Actualizar el estado
            $recarga->update([
                'idestado' => $estadoId,
            ]);
            // Si el estado es "aprobado", actualizar el saldo del cliente
            if ($request->idestado === 'aprobado') {
                $cliente = $recarga->cliente; // Usar la relación con Cliente
                if ($cliente) { // Validar que exista el cliente relacionado
                    $cliente->saldo += $recarga->valor; // Sumar el valor de la recarga al saldo
                    $cliente->save(); // Guardar el nuevo saldo
                    Historial::create([
                        'accion' => 'Recarga-Procesada',
                        'descripcion' =>  'Datos aprobados: ' . json_encode($recarga).' Saldo de cliente: '.$cliente->saldo, // Campo opcional
                        'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
                        'fecha' => now(),
                    ]);
                }
            }
            DB::commit(); // Confirmar cambios
            return redirect()->route('empleado.recargas.index')->with('success', 'Estado de la recarga actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir cambios si hay un error
            return redirect()->route('empleado.recargas.index')
                ->withErrors(['error' => 'Error al actualizar la recarga: ' . $e->getMessage()]);
        }
    }

    public function recargarSaldo()
    {
        $bancos = Banco::all(); // Obtiene todos los bancos
        return view('shopping.recargas', compact('bancos'));
    }

    public function procesarRecarga(Request $request)
    {
        $request->validate([
            'idban' => 'required|exists:bancos,idban',
            'numcomprobante' => 'required|string|max:255|unique:recargas,numcomprobante',
            'valor' => 'required|numeric|min:1',
            'foto' => 'required|image|max:2048',
        ], [
            'numcomprobante.unique' => 'Comprobante ya enviado',
        ]);

        // Obtener el archivo
        $file = $request->file('foto');

        // Definir la carpeta destino (public/storage/fotos)
        $destinationPath = public_path('storage/comprobantes');
        // Crear un nombre único para el archivo
        $filename = time() . '_' . $file->getClientOriginalName();

        // Mover el archivo a la carpeta destino
        $file->move($destinationPath, $filename);

        $recarga = Recarga::create([
            'idcli' => Auth::guard('cliente')->user()->idcli,
            'idban' => $request->idban,
            'numcomprobante' => $request->numcomprobante,
            'valor' => $request->valor,
            'foto' => 'comprobantes/' . $filename,
            'idestado' => 1, // Estado inicial, por ejemplo "Pendiente"
        ]);
        Historial::create([
            'accion' => 'Recarga-Pendiente',
            'descripcion' =>  'Solicitud de la recarga: ' . json_encode($recarga), // Campo opcional
            'realizado_por' => Auth::guard('cliente')->user()->nombrecli.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);
        return redirect()->route('recargar.index')->with('success', '¡Recarga enviada con éxito, por favor tener paciencia!');
    }
}
