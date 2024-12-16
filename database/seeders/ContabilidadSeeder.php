<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ContabilidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insertar los datos proporcionados en la tabla contabilidad
        DB::table('contabilidad')->insert([
            [
                'mes' => 9,   // Septiembre
                'año' => 2023,
                'detalle' => 'Sep-23',
                'num_cuentas' => 5,   // Asumiendo que el número de cuentas es 10
                'num_usuarios' => 20,   // Asumiendo que el número de usuarios es 5
                'ingresos' => 115.00,
                'costos' => 36.22,
                'ganancias' => 78.78,
                'renta' => 0.68,  // Ejemplo de valor de renta
                'num_ventas' => 20
            ],
            [
                'mes' => 10,   // Octubre
                'año' => 2023,
                'detalle' => 'Oct-23',
                'num_cuentas' => 12,
                'num_usuarios' => 6,
                'ingresos' => 381.00,
                'costos' => 187.21,
                'ganancias' => 193.79,
                'renta' => 0.51,
                'num_ventas' => 120
            ],
            [
                'mes' => 11,   // Noviembre
                'año' => 2023,
                'detalle' => 'Nov-23',
                'num_cuentas' => 8,
                'num_usuarios' => 4,
                'ingresos' => 152.00,
                'costos' => 82.54,
                'ganancias' => 69.46,
                'renta' => 0.54,
                'num_ventas' => 70
            ],
            [
                'mes' => 12,   // Diciembre
                'año' => 2023,
                'detalle' => 'Dec-23',
                'num_cuentas' => 9,
                'num_usuarios' => 5,
                'ingresos' => 178.00,
                'costos' => 122.63,
                'ganancias' => 55.37,
                'renta' => 0.31,
                'num_ventas' => 50
            ],
            [
                'mes' => 1,   // Enero
                'año' => 2024,
                'detalle' => 'Jan-24',
                'num_cuentas' => 15,
                'num_usuarios' => 8,
                'ingresos' => 276.00,
                'costos' => 114.03,
                'ganancias' => 161.97,
                'renta' => 0.58,
                'num_ventas' => 120
            ],
            [
                'mes' => 2,   // Febrero
                'año' => 2024,
                'detalle' => 'Feb-24',
                'num_cuentas' => 10,
                'num_usuarios' => 6,
                'ingresos' => 352.00,
                'costos' => 170.00,
                'ganancias' => 182.00,
                'renta' => 0.52,
                'num_ventas' => 110
            ],
            [
                'mes' => 3,   // Marzo
                'año' => 2024,
                'detalle' => 'Mar-24',
                'num_cuentas' => 11,
                'num_usuarios' => 7,
                'ingresos' => 333.00,
                'costos' => 156.60,
                'ganancias' => 176.40,
                'renta' => 0.53,
                'num_ventas' => 140
            ],
            [
                'mes' => 4,   // Abril
                'año' => 2024,
                'detalle' => 'Apr-24',
                'num_cuentas' => 9,
                'num_usuarios' => 5,
                'ingresos' => 329.00,
                'costos' => 149.36,
                'ganancias' => 179.64,
                'renta' => 0.55,
                'num_ventas' => 120
            ],
            [
                'mes' => 5,   // Mayo
                'año' => 2024,
                'detalle' => 'May-24',
                'num_cuentas' => 14,
                'num_usuarios' => 10,
                'ingresos' => 394.00,
                'costos' => 125.04,
                'ganancias' => 268.96,
                'renta' => 0.32,
                'num_ventas' => 160
            ],
            [
                'mes' => 6,   // Junio
                'año' => 2024,
                'detalle' => 'Jun-24',
                'num_cuentas' => 13,
                'num_usuarios' => 8,
                'ingresos' => 370.00,
                'costos' => 154.53,
                'ganancias' => 215.47,
                'renta' => 0.42,
                'num_ventas' => 150
            ],
            [
                'mes' => 7,   // Julio
                'año' => 2024,
                'detalle' => 'Jul-24',
                'num_cuentas' => 12,
                'num_usuarios' => 7,
                'ingresos' => 361.00,
                'costos' => 170.07,
                'ganancias' => 190.93,
                'renta' => 0.47,
                'num_ventas' => 150
            ],
            [
                'mes' => 8,   // Agosto
                'año' => 2024,
                'detalle' => 'Aug-24',
                'num_cuentas' => 25,
                'num_usuarios' => 20,
                'ingresos' => 2252.12,
                'costos' => 735.59,
                'ganancias' => 1516.53,
                'renta' => 0.33,
                'num_ventas' => 340
            ],
            [
                'mes' => 9,   // Septiembre
                'año' => 2024,
                'detalle' => 'Sep-24',
                'num_cuentas' => 18,
                'num_usuarios' => 12,
                'ingresos' => 1200.00,
                'costos' => 460.00,
                'ganancias' => 740.00,
                'renta' => 0.38,
                'num_ventas' => 220
            ],
            [
                'mes' => 10,   // Octubre
                'año' => 2024,
                'detalle' => 'Oct-24',
                'num_cuentas' => 10,
                'num_usuarios' => 5,
                'ingresos' => 500.00,
                'costos' => 200.00,
                'ganancias' => 300.00,
                'renta' => 0.40,
                'num_ventas' => 180
            ],
            [
                'mes' => 11,   // Noviembre
                'año' => 2024,
                'detalle' => 'Nov-24',
                'num_cuentas' => 0,   // No hay datos para este mes
                'num_usuarios' => 0,
                'ingresos' => 180.00,
                'costos' => 100.00,
                'ganancias' => 0.00,
                'renta' => 0.00,
                'num_ventas' => 130
            ]
        ]);
    }
}
