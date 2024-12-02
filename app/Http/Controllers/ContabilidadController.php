<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\Empleado;
use App\Models\ViewClientesUsuarios;
use App\Models\ViewUsuarioActivo;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ContabilidadController extends Controller
{
    public function index(Request $request)
    {
        // Obtener todas las ventas con los detalles de cada una
        $ventas = Venta::with(['detalles_venta'])->orderBy('fechaven')->get();
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;
        $usuarios = ViewUsuarioActivo::all();

        $usuarios_acobrar = 0;
        foreach ($usuarios as $usuario) {
            $fechaVencimiento = \Carbon\Carbon::parse($usuario->fecha_vencimiento);
            $hoy = \Carbon\Carbon::today();
            $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);
            if($diasRestantes <= 3){
                $usuarios_acobrar+=1;
            }
        }
        $clientes_activos = ViewClientesUsuarios::count();
        $usuarios_activos = ViewUsuarioActivo::count();
        $cuentas_caidas = Cuenta::where('caidacue', true)->count();

        $ingresos_mes = Venta::whereMonth('fechaven', $month)->whereYear('fechaven', $year)->sum('totalpagoven');
        //$ingresos_mes = calcular_ingresos_mes($ventas); //para evitar varias consultas y reducir costos
        $ingresos_ano = Venta::whereYear('fechaven', $year)->sum('totalpagoven');
        // Pasar las ventas y los detalles de venta a la vista
        return view('dashboard', compact('ventas', 'ingresos_mes', 'ingresos_ano', 'clientes_activos', 
            'usuarios_activos', 'cuentas_caidas', 'usuarios_acobrar'));
    }
    /*
    public function calcular_ingresos_mes($ventas){ //para evitar varias consultas y reducir costos
        $ingresos_mes = 0;
        $month = Carbon::now()->month;
        foreach ($ventas as $venta) {
            if(Carbon::parse($venta->fechaven)->month == $month){
                $ingresos_mes +=$venta->totalpagoven;
            }
        }
        return $ingresos_mes;
    }
    */
}
