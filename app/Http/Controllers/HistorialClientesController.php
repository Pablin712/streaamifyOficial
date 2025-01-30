<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DetalleVenta;
use App\Models\Recarga;

class HistorialClientesController extends Controller
{
    public function index()
    {
        $idcli = Auth::guard('cliente')->user()->idcli;

        // Obtener los detalles de venta del cliente logueado
        $detallesVenta = DetalleVenta::with('perfil')
            ->whereHas('venta', function ($query) use ($idcli) {
                $query->where('idcli', $idcli);
            })
            ->get();

        // Obtener las recargas del cliente logueado
    $recargas = Recarga::where('idcli', $idcli)->with('estado')->orderBy('created_at', 'desc')->paginate(10); // 10 recargas por página

        return view('shopping.historialCliente', compact('detallesVenta', 'recargas'));
    }
}