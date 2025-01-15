<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyStatistic; // Modelo de la tabla diaria
use Carbon\Carbon;
use App\Models\ViewUsuarioActivo;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Costo;
use App\Models\Gasto;
class SaveDailyStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Guarda estadísticas diarias de la aplicación';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        // Guardar el registro en la base de datos
        // 1. Número de usuarios activos
        $activeUsers = ViewUsuarioActivo::count();

        // 2. Ingresos del día
        $dailyRevenue = Venta::whereDate('created_at', $today)->sum('totalpagoven'); // Ajusta el campo 'total' según tu modelo.

        // 3. Costos del día
        $dailyCost = Costo::whereDate('fechacos', $today)->sum('montocos');

        // 4. Gastos del día
        $dailyBill = Gasto::whereDate('created_at', $today)->sum('montogas');

        // 5. Total de ventas del día
        $dailySales = Venta::whereDate('created_at', $today)->count();

        // 6. Nuevos clientes registrados
        $newCustomers = Cliente::whereDate('created_at', $today)->count();

        // Guardar los datos en la base de datos
        DailyStatistic::updateOrCreate(
            ['date' => $today], // Fecha única
            [
                'active_users' => $activeUsers,
                'daily_revenue' => $dailyRevenue,
                'daily_cost' => $dailyCost,
                'daily_bill' => $dailyBill,
                'daily_sales' => $dailySales,
                'new_customers' => $newCustomers,
            ]
        );

        $this->info("Estadísticas diarias guardadas: 
            Usuarios activos: $activeUsers, 
            Ingresos: $dailyRevenue, 
            Costos: $dailyCost,
            Gastos: $dailyBill,
            Ventas: $dailySales, 
            Nuevos clientes: $newCustomers.");
    }
}
