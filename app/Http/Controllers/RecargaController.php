<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banco;
use App\Models\Recarga;
use Illuminate\Support\Facades\Auth;

class RecargaController extends Controller
{
    public function index()
    {
        // Obtener las recargas con relaciones (cliente, estado y banco)
        $recargas = Recarga::with(['cliente', 'estado', 'banco'])->get();

        return view('sales.recargas.index', compact('recargas'));
    }

    public function updateEstado(Request $request, $idrec)
    {
        $recarga = Recarga::findOrFail($idrec);
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
            }
        }
        return redirect()->route('empleado.recargas.index')->with('success', 'Estado de la recarga actualizado exitosamente.');
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

        Recarga::create([
            'idcli' => Auth::guard('cliente')->user()->idcli,
            'idban' => $request->idban,
            'numcomprobante' => $request->numcomprobante,
            'valor' => $request->valor,
            'foto' => 'comprobantes/' . $filename,
            'idestado' => 1, // Estado inicial, por ejemplo "Pendiente"
        ]);

        return redirect()->route('recargar.index')->with('success', '¡Recarga enviada con éxito, por favor tener paciencia!');
    }
}
