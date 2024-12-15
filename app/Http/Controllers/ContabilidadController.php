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
use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;

class ContabilidadController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeRole(['administrador', 'contador']);
        // Obtener todas las ventas con los detalles de cada una
        $ventas = Venta::with(['detalles_venta'])->orderBy('fechaven')->get();
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;
        $total_usuarios_activos = ViewUsuarioActivo::count();
        $usuarios = ViewUsuarioActivo::all();

        $usuarios_acobrar = 0;
        //$usuarios_activos = 0;
        foreach ($usuarios as $usuario) {
            $fechaVencimiento = \Carbon\Carbon::parse($usuario->fecha_vencimiento);
            $hoy = \Carbon\Carbon::today();
            $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);
            if ($diasRestantes <= 3) {
                $usuarios_acobrar += 1;
            }
            //$usuarios_activos+=1;
        }
        $clientes_activos = ViewClientesUsuarios::count();
        $cuentas_caidas = Cuenta::where('caidacue', true)->count();

        $ingresos_mes = Venta::whereMonth('fechaven', $month)->whereYear('fechaven', $year)->sum('totalpagoven');
        //$ingresos_mes = calcular_ingresos_mes($ventas); //para evitar varias consultas y reducir costos
        $ingresos_ano = Venta::whereYear('fechaven', $year)->sum('totalpagoven');
        // Pasar las ventas y los detalles de venta a la vista

        $promedio_pagos_mes = Venta::whereMonth('fechaven', $month)->whereYear('fechaven', $year)->avg('totalpagoven');
        $cliente_mas_facturado = ViewClientesUsuarios::orderByDesc('facturado')->select('nombre_cliente', 'facturado')->first();
        $num_cuentas = Cuenta::all()->count();
        $costos_mes = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->sum('montocos');
        $ventas_mes = Venta::whereMonth('fechaven', $month)->whereYear('fechaven', $year)->count();
        $ventas_ano = Venta::whereYear('fechaven', $year)->count();

        $cuentas = Cuenta::with(['valor'])->orderBy('fechavencue')->get();
        $espacios = 0;
        foreach ($cuentas as $cuenta) {
            $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->count();
            $cuenta->usuarios_activos = $usuarios;
            $pantmaxval = $cuenta->valor->pantmaxval;
            $usuarios_activos = $cuenta->usuarios_activos;
            $resta = $pantmaxval - $usuarios_activos;
            $espacios += $resta;
        }


        $cuentas_netflix = Cuenta::where('idcue', 'like', 'NETFLIX%')->count();
        $usuarios_netflix = ViewUsuarioActivo::where('idcue', 'like', 'NETFLIX%')->count();
        $ingresos_netflix = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'NETFLIX%')
            ->sum('montodet');
        $costos_netflix = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'NETFLIX%')->sum('montocos');



        $cuentas_disney = Cuenta::where('idcue', 'like', 'DISNEY%')->count();
        $usuarios_disney = ViewUsuarioActivo::where('idcue', 'like', 'DISNEY%')->count();
        $ingresos_disney = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'DISNEY%')
            ->sum('montodet');
        $costos_disney = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'DISNEY%')->sum('montocos');



        $cuentas_prime = Cuenta::where('idcue', 'like', 'PRIME%')->count();
        $usuarios_prime = ViewUsuarioActivo::where('idcue', 'like', 'PRIME%')->count();
        $ingresos_prime = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'PRIME%')
            ->sum('montodet');
        $costos_prime = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'PRIME%')->sum('montocos');


        $cuentas_max = Cuenta::where('idcue', 'like', 'MAX%')->count();
        $usuarios_max = ViewUsuarioActivo::where('idcue', 'like', 'MAX%')->count();
        $ingresos_max = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'MAX%')
            ->sum('montodet');
        $costos_max = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'MAX%')->sum('montocos');



        $cuentas_magis = Cuenta::where('idcue', 'like', 'MAGIS%')->count();
        $usuarios_magis = ViewUsuarioActivo::where('idcue', 'like', 'MAGIS%')->count();
        $ingresos_magis = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'MAGIS%')
            ->sum('montodet');
        $costos_magis = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'MAGIS%')->sum('montocos');


        $cuentas_crunchy = Cuenta::where('idcue', 'like', 'CRUNCHY%')->count();
        $usuarios_crunchy = ViewUsuarioActivo::where('idcue', 'like', 'CRUNCHY%')->count();
        $ingresos_crunchy = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'CRUNCHY%')
            ->sum('montodet');
        $costos_crunchy = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'CRUNCHY%')->sum('montocos');


        $cuentas_paramount = Cuenta::where('idcue', 'like', 'PARAMOUNT%')->count();
        $usuarios_paramount = ViewUsuarioActivo::where('idcue', 'like', 'PARAMOUNT%')->count();
        $ingresos_paramount = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'PARAMOUNT%')
            ->sum('montodet');
        $costos_paramount = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'PARAMOUNT%')->sum('montocos');



        $cuentas_spotify = Cuenta::where('idcue', 'like', 'SPOTIFY%')->count();
        $usuarios_spotify = ViewUsuarioActivo::where('idcue', 'like', 'SPOTIFY%')->count();
        $ingresos_spotify = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'SPOTIFY%')
            ->sum('montodet');
        $costos_spotify = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'SPOTIFY%')->sum('montocos');




        $cuentas_otros = Cuenta::where('idcue', 'not like', 'NETFLIX%')
            ->where('idcue', 'not like', 'DISNEY%')
            ->where('idcue', 'not like', 'PRIME%')
            ->where('idcue', 'not like', 'MAX%')
            ->where('idcue', 'not like', 'MAGIS%')
            ->where('idcue', 'not like', 'CRUNCHY%')
            ->where('idcue', 'not like', 'PARAMOUNT%')
            ->where('idcue', 'not like', 'SPOTIFY%')
            ->count();
        $usuarios_otros = ViewUsuarioActivo::where('idcue', 'not like', 'NETFLIX%')
            ->where('idcue', 'not like', 'DISNEY%')
            ->where('idcue', 'not like', 'PRIME%')
            ->where('idcue', 'not like', 'MAX%')
            ->where('idcue', 'not like', 'MAGIS%')
            ->where('idcue', 'not like', 'CRUNCHY%')
            ->where('idcue', 'not like', 'PARAMOUNT%')
            ->where('idcue', 'not like', 'SPOTIFY%')
            ->count();
        $ingresos_otros = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'not like', 'NETFLIX%')
            ->where('idper', 'not like', 'DISNEY%')
            ->where('idper', 'not like', 'PRIME%')
            ->where('idper', 'not like', 'MAX%')
            ->where('idper', 'not like', 'MAGIS%')
            ->where('idper', 'not like', 'CRUNCHY%')
            ->where('idper', 'not like', 'PARAMOUNT%')
            ->where('idper', 'not like', 'SPOTIFY%')
            ->sum('montodet');
        $costos_otros = Costo::whereMonth('fechacos', $month)
            ->whereYear('fechacos', $year)
            ->where('idcue', 'not like', 'NETFLIX%')
            ->where('idcue', 'not like', 'DISNEY%')
            ->where('idcue', 'not like', 'PRIME%')
            ->where('idcue', 'not like', 'MAX%')
            ->where('idcue', 'not like', 'MAGIS%')
            ->where('idcue', 'not like', 'CRUNCHY%')
            ->where('idcue', 'not like', 'PARAMOUNT%')
            ->where('idcue', 'not like', 'SPOTIFY%')
            ->sum('montocos');


        $contabilidad = Contabilidad::orderByDesc('año')
            ->orderByDesc('mes')
            ->take(6)
            ->get();

        // Crear los arrays para los meses, ingresos, costos y ganancias
        $meses_historial = $contabilidad->map(function ($item) {
            // Devolver el nombre del mes y año
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
            'total_usuarios_activos',
            'cuentas_caidas',
            'usuarios_acobrar',
            'num_cuentas',
            'costos_mes',
            'promedio_pagos_mes',
            'cliente_mas_facturado',
            'ventas_mes',
            'ventas_ano',
            'espacios',

            'meses_historial',
            'ingresos_historial',
            'costos_historial',
            'ganancias_historial',

            'cuentas_netflix',
            'usuarios_netflix',
            'ingresos_netflix',
            'costos_netflix',

            'cuentas_disney',
            'usuarios_disney',
            'ingresos_disney',
            'costos_disney',

            'cuentas_prime', // Aquí es donde agregas las variables correspondientes
            'usuarios_prime',
            'ingresos_prime',
            'costos_prime',

            'cuentas_max',
            'usuarios_max',
            'ingresos_max',
            'costos_max',

            'cuentas_magis',
            'usuarios_magis',
            'ingresos_magis',
            'costos_magis',

            'cuentas_crunchy',
            'usuarios_crunchy',
            'ingresos_crunchy',
            'costos_crunchy',

            'cuentas_paramount',
            'usuarios_paramount',
            'ingresos_paramount',
            'costos_paramount',

            'cuentas_spotify',
            'usuarios_spotify',
            'ingresos_spotify',
            'costos_spotify',

            'cuentas_otros', // Agrega la variable para "otros"
            'usuarios_otros',
            'ingresos_otros',
            'costos_otros'
        ));
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

        return $meses[$mes] ?? 'Mes Desconocido'; // Retorna el nombre del mes
    }
    public function store(Request $request)
    {
        // Acceder a las variables enviadas
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
        $ventas_mes = $request->input('ventas_mes');

        if ($costos_mes != 0) {
            $renta_variable = number_format($ingresos_mes / $costos_mes, 2);
        } else {
            $renta_variable = 0;
        }

        $mes = now()->month;  // Obtiene el mes actual (1-12)
        $ano = now()->year;
        $detalle = now()->format('M-y');

        Contabilidad::updateOrCreate(
            [
                'mes' => $mes,               // Condición para buscar el registro
                'año' => $ano,               // Condición para buscar el registro
            ],
            [
                'detalle' => $detalle,       // 'Jul-24' o el formato que necesites
                'num_cuentas' => $num_cuentas,
                'num_usuarios' => $usuarios_activos,
                'ingresos' => $ingresos_mes,
                'costos' => $costos_mes,
                'num_ventas' => $ventas_mes,
                'ganancias' => $ingresos_mes - $costos_mes,  // Si necesitas calcular las ganancias
                'renta' => $renta_variable,
            ]
        );

        return redirect()->route('dashboard')->with('success', 'Reporte guardado con éxito.');
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
