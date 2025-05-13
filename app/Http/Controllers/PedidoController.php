<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\EstadoRecarga;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PedidoController extends Controller
{
    public function index()
    {
        if (!Gate::allows('empleado.pedidos.index')) {
            abort(403, 'No tienes permiso para ver los pedidos.');
        }
        $pedidos = Pedido::with(['cliente', 'producto', 'estado'])->orderBy('fechapedido', 'desc')->get();
        $estados = EstadoRecarga::all();

        return view('sales.pedidos.index', compact('pedidos', 'estados'));
    }
    public function update(Request $request, $id)
    {
        if (!Gate::allows('empleado.pedidos.update')) {
            abort(403, 'No tienes permiso para aceptar los pedidos.');
        }
        $pedido = Pedido::findOrFail($id);
        $cliente = Cliente::findOrFail($pedido->idcli); // Buscar cliente asociado al pedido
        $producto = Producto::findOrFail($pedido->producto_id); // Buscar producto del pedido

        // Si el pedido fue aprobado, descontar saldo al cliente
        if ($request->idestado == EstadoRecarga::where('nombre', 'Aprobado')->value('idestado')) {
            if ($cliente->saldo >= $producto->preciopro) {
                $cliente->saldo -= $producto->preciopro;
                $cliente->save();
            } else {
                return redirect()->route('empleado.pedidos.index')
                    ->with('error', 'El cliente no tiene suficiente saldo para procesar el pedido.');
            }
        }
        $pedido->idestado = $request->idestado;
        $pedido->respuesta = $request->respuesta;
        $pedido->save();

        return redirect()->route('empleado.pedidos.index')->with('success', 'Pedido actualizado correctamente.');
    }
}
