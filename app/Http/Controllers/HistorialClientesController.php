<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pedido;
use App\Models\Recarga;
use App\Models\Venta;
use App\Models\ViewUsuarioActivo;
class HistorialClientesController extends Controller
{
    public function index()
    {
        $idcli = Auth::guard('cliente')->user()->idcli;
        $pedidos = Pedido::where('idcli',$idcli)->orderBy('fechapedido', 'desc')->paginate(10);;
        $ventas = Venta::with(['detalles_venta'])->where('idcli', $idcli)->orderBy('fechaven', 'desc')->paginate(10);
        $usuarios_activos = ViewUsuarioActivo::where('idcli',$idcli)->orderBy('fecha_vencimiento', 'desc')->paginate(10);
        // Obtener las recargas del cliente logueado
        $recargas = Recarga::where('idcli', $idcli)->with('estado')->orderBy('created_at', 'desc')->paginate(10); // 10 recargas por página

        return view('shopping.historialCliente', compact('ventas', 'recargas', 'pedidos', 'usuarios_activos'));
    }
}
