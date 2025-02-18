<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\EstadoRecarga;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Historial;
class PedidoController extends Controller
{
    public function __construct() {
        $this->middleware('can:empleado.pedidos.index')->only('index');
        $this->middleware('can:empleado.pedidos.update')->only('update');
    }
    public function index()
    {
        $pedidos = Pedido::with(['cliente', 'producto', 'estado'])->orderBy('fechapedido', 'desc')->get();
        $estados = EstadoRecarga::all();

        return view('sales.pedidos.index', compact('pedidos', 'estados'));
    }
    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);
        $cliente = Cliente::findOrFail($pedido->idcli); // Buscar cliente asociado al pedido
        $producto = Producto::findOrFail($pedido->producto_id); // Buscar producto del pedido

        // Si el pedido fue aprobado, descontar saldo al cliente
        if ($request->idestado == EstadoRecarga::where('nombre', 'Aprobado')->value('idestado')) {
            if ($cliente->saldo >= $producto->preciopro) {
                $cliente->saldo -= $producto->preciopro;
                $cliente->save();
                Historial::create([
                    'accion' => 'Pedido Aprobado',
                    'descripcion' =>  'Pedido atentido de cliente. Datos: ' . json_encode($cliente), // Campo opcional
                    'realizado_por' => $cliente->nombrecli.' | '. $request->ip(), // Almacena el nombre del usuario
                    'fecha' => now(),
                ]);
            } else {
                Historial::create([
                    'accion' => 'Pedido Fallido',
                    'descripcion' =>  'Pedido fallido de cliente. Datos: ' . json_encode($cliente), // Campo opcional
                    'realizado_por' => $cliente->nombrecli.' | '. $request->ip(), // Almacena el nombre del usuario
                    'fecha' => now(),
                ]);
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
