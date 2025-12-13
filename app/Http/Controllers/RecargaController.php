<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banco;
use App\Models\Recarga;
use App\Models\Historial;
use App\Models\Empleado;
use App\Services\BancoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NotificacionNueva;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

class RecargaController extends Controller
{
    protected $bancoService;

    public function __construct(BancoService $bancoService)
    {
        $this->bancoService = $bancoService;
    }

    public function index()
    {
        if (!Gate::allows('empleado.recargas.index')) {
            abort(403, 'No tienes permiso para ver las recargas.');
        }
        // Obtener las recargas con relaciones (cliente, estado y banco)
        $recargas = Recarga::with(['cliente', 'estado', 'banco'])->orderBy('created_at', 'desc')->get();

        return view('sales.recargas.index', compact('recargas'));
    }

    public function updateEstado(Request $request, $id)
    {
        if (!Gate::allows('empleado.recargas.updateEstado')) {
            abort(403, 'No tienes permiso para aprobar o rechazar las recargas.');
        }

        try {
            DB::beginTransaction(); // Iniciar transacción

            $recarga = Recarga::where('idrec', $id)
                ->where('idestado', 1) // Solo procesar si sigue pendiente
                ->lockForUpdate() // Bloquea la fila hasta que termine la transacción
                ->first();

            if (!$recarga) {
                DB::rollBack();
                return redirect()->route('empleado.recargas.index')
                    ->withErrors(['error' => 'La recarga ya fue procesada por otro usuario o no existe.']);
            }

            // Validar que el estado sea "aprobado" o "rechazado"
            $request->validate([
                'idestado' => 'required|in:2,3', // 2 = Rechazado, 3 = Aprobado
            ]);

            // Actualizar el estado
            $recarga->update([
                'idestado' => $request->idestado,
            ]);

            // Si el estado es "aprobado" (3), actualizar el saldo del cliente
            if ($request->idestado == 3) { // Comparar con el texto, no con número
                $cliente = $recarga->cliente; // Usar la relación con Cliente

                if ($cliente) { // Validar que exista el cliente relacionado
                    $cliente->saldo += $recarga->valor; // Sumar el valor de la recarga al saldo
                    $cliente->save(); // Guardar el nuevo saldo

                    // Registrar transacción bancaria (ingreso) si la recarga tiene banco asociado
                    if ($recarga->idban) {
                        try {
                            $transaccion = $this->bancoService->registrarTransaccion(
                                $recarga->idban,
                                $recarga->valor,
                                'ingreso',
                                'Recarga #' . $recarga->idrec . ' - Cliente: ' . $cliente->nombrecli
                            );

                            $recarga->transaccion_id = $transaccion->id;
                            $recarga->save();
                        } catch (\Exception $e) {
                            // Si falla, revertir el saldo del cliente
                            $cliente->saldo -= $recarga->valor;
                            $cliente->save();
                            return redirect()->route('recargas.index')->with('error', $e->getMessage());
                        }
                    }

                    Historial::create([
                        'accion' => 'Recarga-Procesada',
                        'descripcion' => 'Recarga ID: ' . $recarga->idrec . ' aprobada. Valor: $' . $recarga->valor . '. Nuevo saldo cliente: $' . $cliente->saldo,
                        'empleado_id' => Auth::user()->idemp,
                        'created_at' => now(),
                    ]);
                }
            } else {
                // Registrar el rechazo en el historial
                Historial::create([
                    'accion' => 'Recarga-Rechazada',
                    'descripcion' => 'Recarga ID: ' . $recarga->idrec . ' rechazada. Valor: $' . $recarga->valor,
                    'empleado_id' => Auth::user()->idemp,
                    'created_at' => now(),
                ]);
            }

            DB::commit(); // Confirmar cambios

            $mensaje = $request->idestado == 3
                ? 'Recarga aprobada exitosamente.'
                : 'Recarga rechazada exitosamente.';

            return redirect()->route('empleado.recargas.index')->with('success', $mensaje);
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
        // 🔔 Notificar a los empleados
        $empleados = Empleado::all(); // Obtener empleados con el rol adecuado
        Notification::send($empleados, new NotificacionNueva($recarga));

        // ✅ Corrección: Esto sí funciona
        event(new \Illuminate\Support\Facades\Event('notificacionRecibida'));

        return redirect()->route('recargar.index')->with('success', '¡Recarga enviada con éxito, por favor tener paciencia!');
    }
}
