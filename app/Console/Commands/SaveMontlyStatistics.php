<?php

namespace App\Console\Commands;
use App\Models\Contabilidad;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\Costo;
use App\Models\Gasto;
use App\Models\ViewClientesUsuarios;
use App\Models\ViewUsuarioActivo;
use Illuminate\Console\Command;

class SaveMontlyStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Guarda estadísticas mensuales de la aplicación';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mes = now()->month;  // Obtiene el mes actual (1-12)
        $ano = now()->year;
        $detalle = now()->format('M-y');

        $num_cuentas = Cuenta::all()->count();
        $ingresos_mes = Venta::whereMonth('fechaven', $mes)->whereYear('fechaven', $ano)->sum('totalpagoven');
        $total_usuarios_activos = ViewUsuarioActivo::count();
        $costos_mes = Costo::whereMonth('fechacos', $mes)->whereYear('fechacos', $ano)->sum('montocos');
        $gastos_mes = Gasto::whereMonth('fechagas', $mes)->whereYear('fechagas', $ano)->sum('montogas');
        $ventas_mes = Venta::whereMonth('fechaven', $mes)->whereYear('fechaven', $ano)->count();
        if ($costos_mes != 0) {
            $renta_variable = number_format($ingresos_mes / $costos_mes, 2);
        } else {
            $renta_variable = 0.00;
        }
        $ganancias = $ingresos_mes - $costos_mes - $gastos_mes;

        Contabilidad::updateOrCreate(
            [
                'mes' => $mes,               // Condición para buscar el registro
                'año' => $ano,               // Condición para buscar el registro
            ],
            [
                'detalle' => $detalle,       // 'Jul-24' o el formato que necesites
                'num_cuentas' => $num_cuentas,
                'num_usuarios' => $total_usuarios_activos,
                'ingresos' => $ingresos_mes,
                'costos' => $costos_mes,
                'gastos' => $gastos_mes,
                'num_ventas' => $ventas_mes,
                'ganancias' => $ganancias,  // Si necesitas calcular las ganancias
                'renta' => $renta_variable,
            ]
        );
        $this->info("Estadísticas mensuales guardadas: 
            Mes: $mes, 
            Año: $ano, 
            Numero de cuentas: $num_cuentas,
            Numero de usuarios: $total_usuarios_activos,
            Ingresos: $ingresos_mes,
            Costos: $costos_mes,
            Gastos: $gastos_mes,
            Total de ventas: $ventas_mes,
            Ganancias: $ganancias,
            Renta: $renta_variable.
            ");
    }
}
