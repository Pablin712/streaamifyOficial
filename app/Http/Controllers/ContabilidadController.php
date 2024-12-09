<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\Empleado;
use App\Models\Costo;
use App\Models\ViewClientesUsuarios;
use App\Models\ViewUsuarioActivo;
use App\Models\Contabilidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ContabilidadController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeRole(['administrador', 'contador']);

        // Obtener todas las ventas con los detalles de cada una
        $ventas = Venta::with(['detalles_venta'])->orderBy('fechaven')->get();
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;
        $usuarios = ViewUsuarioActivo::all();

        $usuarios_acobrar = 0;
        foreach ($usuarios as $usuario) {
            $fechaVencimiento = Carbon::parse($usuario->fecha_vencimiento);
            $hoy = Carbon::today();
            $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);
            if ($diasRestantes <= 3) {
                $usuarios_acobrar += 1;
            }
        }

        $clientes_activos = ViewClientesUsuarios::count();
        $usuarios_activos = ViewUsuarioActivo::count();
        $cuentas_caidas = Cuenta::where('caidacue', true)->count();

        $ingresos_mes = Venta::whereMonth('fechaven', $month)->whereYear('fechaven', $year)->sum('totalpagoven');
        $ingresos_ano = Venta::whereYear('fechaven', $year)->sum('totalpagoven');
        $promedio_pagos_mes = Venta::whereMonth('fechaven', $month)->whereYear('fechaven', $year)->avg('totalpagoven');
        $cliente_mas_facturado = ViewClientesUsuarios::orderByDesc('facturado')->select('nombre_cliente', 'facturado')->first();
        $num_cuentas = Cuenta::all()->count();
        $costos_mes = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->sum('montocos');

        $contabilidad = Contabilidad::orderByDesc('año')
            ->orderByDesc('mes')
            ->take(6)
            ->get();

        $meses_historial = $contabilidad->map(function ($item) {
            return $this->getMonthName($item->mes) . ' ' . $item->año;
        });

        $ingresos_historial = $contabilidad->pluck('ingresos');
        $costos_historial = $contabilidad->pluck('costos');
        $ganancias_historial = $contabilidad->pluck('ganancias');

        return view('dashboard', compact(
            'ventas',
            'ingresos_mes',
            'ingresos_ano',
            'clientes_activos',
            'usuarios_activos',
            'cuentas_caidas',
            'usuarios_acobrar',
            'num_cuentas',
            'costos_mes',
            'promedio_pagos_mes',
            'cliente_mas_facturado',
            'meses_historial',
            'ingresos_historial',
            'costos_historial',
            'ganancias_historial'
        ));
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['administrador', 'contador']);

        $ventas = $request->input('ventas');
        $ingresos_mes = $request->input('ingresos_mes');
        $ingresos_ano = $request->input('ingresos_ano');
        $clientes_activos = $request->input('clientes_activos');
        $usuarios_activos = $request->input('usuarios_activos');
        $cuentas_caidas = $request->input('cuentas_caidas');
        $usuarios_acobrar = $request->input('usuarios_acobrar');
        $num_cuentas = $request->input('num_cuentas');
        $costos_mes = $request->input('costos_mes');
        $promedio_pagos_mes = $request->input('promedio_pagos_mes');
        $cliente_mas_facturado = $request->input('cliente_mas_facturado');

        $mes = now()->month;
        $ano = now()->year;
        $detalle = now()->format('M-y');

        Contabilidad::updateOrCreate(
            [
                'mes' => $mes,
                'año' => $ano,
            ],
            [
                'detalle' => $detalle,
                'num_cuentas' => $num_cuentas,
                'num_usuarios' => $usuarios_activos,
                'ingresos' => $ingresos_mes,
                'costos' => $costos_mes,
            ]
        );

        return redirect()->route('dashboard')->with('success', 'Reporte guardado con éxito.');
    }

    private function getMonthName($mes)
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        return $meses[$mes] ?? 'Mes Desconocido';
    }

    private function authorizeRole(array $roles)
    {
        
            $userRole = Auth::user()->idrol;
    
            if (!in_array($userRole, $roles)) {
                // Redirigir a la vista anterior con una alerta
                return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.')->send();
            }
        
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
