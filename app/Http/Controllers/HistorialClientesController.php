<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pedido;
use App\Models\Recarga;
use App\Models\Soporte;
use App\Models\Venta;
use App\Models\ViewUsuarioActivo;
use App\Services\NetflixCodigoService;

class HistorialClientesController extends Controller
{
    public function __construct(private NetflixCodigoService $netflixCodigoService)
    {
    }

    public function index()
    {
        $idcli = Auth::guard('cliente')->user()->idcli;
        $pedidos = Pedido::where('idcli',$idcli)->orderBy('fechapedido', 'desc')->paginate(10);;
        $ventas = Venta::with(['detalles_venta.perfil.cuenta.valor.servicio'])->where('idcli', $idcli)->orderBy('fechaven', 'desc')->paginate(10);
        $usuarios_activos = ViewUsuarioActivo::with(['cuenta.valor.servicio', 'cuenta.valor.proveedor', 'venta.detalles_venta.perfil.cuenta.valor.servicio', 'profile'])->where('idcli',$idcli)->orderBy('fecha_vencimiento', 'desc')->paginate(20);
        $referidos = Cliente::where('referido_por', $idcli)->orderBy('created_at', 'desc')->paginate(10);
        $soportes = Soporte::with(['cuenta.valor.servicio'])
            ->where('idcli', $idcli)
            ->orderByRaw("CASE WHEN estado = 'pendiente' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->get();
        $cuentasSoporte = ViewUsuarioActivo::with(['cuenta.valor.servicio'])
            ->where('idcli', $idcli)
            ->orderBy('fecha_vencimiento', 'desc')
            ->get()
            ->unique('idcue')
            ->values();
        // Obtener las recargas del cliente logueado
        $recargas = Recarga::where('idcli', $idcli)->with('estado')->orderBy('created_at', 'desc')->paginate(10); // 10 recargas por página

        return view('shopping.historialCliente', compact('ventas', 'recargas', 'pedidos', 'usuarios_activos', 'referidos', 'soportes', 'cuentasSoporte'));
    }

    public function pedirCodigoNetflix(Request $request, $iddet)
    {
        $cliente = Auth::guard('cliente')->user();

        $usuarioActivo = ViewUsuarioActivo::with(['cuenta.valor.servicio', 'cuenta.valor.proveedor'])
            ->where('iddet', $iddet)
            ->where('idcli', $cliente->idcli)
            ->firstOrFail();

        $cuenta = $usuarioActivo->cuenta;
        $resultado = $this->netflixCodigoService->requestCode($cuenta, [
            'type' => 'cliente',
            'id' => $cliente->idcli,
            'name' => $cliente->nombrecli,
            'email' => $cliente->email,
            'phone' => $cliente->telefonocli,
            'country' => $cliente->pais,
        ]);

        if (!($resultado['success'] ?? false)) {
            return response()->json($resultado, 422);
        }

        return response()->json($resultado);
    }
}
