<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\EstadoRecarga;
class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with(['cliente', 'producto', 'estado'])->orderBy('fechapedido', 'desc')->get();
        $estados = EstadoRecarga::all();

        return view('sales.pedidos.index', compact('pedidos', 'estados'));
    }
    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $pedido->idestado = $request->idestado;
        $pedido->respuesta = $request->respuesta;
        $pedido->save();

        return redirect()->route('empleado.pedidos.index')->with('success', 'Pedido actualizado correctamente.');
    }
}
