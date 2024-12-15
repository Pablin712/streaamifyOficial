<?php

namespace Database\Factories;

use App\Models\Venta;
use App\Models\Empleado;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DetalleVenta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Venta>
 */
class VentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Obtener la fecha de venta aleatoria
        $fechaVenta = $this->faker->dateTimeBetween('2024-11-25', '2025-01-13');
        $fechaVentaFormatted = Carbon::parse($fechaVenta)->format('dmy'); // Formato de fecha 'ddmmyy'
        // Obtener el contador de ventas para el día actual
        $ventaDiaria = DB::table('ventas_diarias')->first();

        if (!$ventaDiaria) {
            // Si no existe, creamos un nuevo registro para hoy con el contador inicializado
            DB::table('ventas_diarias')->insert([
                'fecha' => Carbon::today()->toDateString(),
                'numero_venta' => 2,  // Iniciamos el contador en 1
            ]);
            $numeroVenta = 1;
        } else {
            // Si ya existe un registro, incrementamos el contador
            $numeroVenta = $ventaDiaria->numero_venta;
            DB::table('ventas_diarias')
                ->where('fecha', Carbon::today()->toDateString())
                ->update(['numero_venta' => $numeroVenta + 1]);
        }

        // Generar el ID de venta en el formato deseado
        $idVenta = 'FAC' . str_pad($numeroVenta, 3, '0', STR_PAD_LEFT) . '-' . $fechaVentaFormatted;

        // Crear la venta inicialmente con totalpagoven = 0
        $venta = Venta::make([
            'idemp' => Empleado::inRandomOrder()->first()->idemp, // Relación aleatoria con un empleado
            'idcli' => Cliente::inRandomOrder()->first()->idcli, // Relación aleatoria con un cliente
            'fechaven' => $fechaVenta, // Fecha aleatoria dentro de este año
            'idven' => $idVenta, // Asignar el ID generado
            'totalpagoven' => 0, // Inicializamos el total como 0
        ]);

        // Generar detalles de venta, pasándole la venta creada
        $detalles = DetalleVenta::factory()->count($this->faker->numberBetween(1, 7))->make([
            'idven' => $venta->idven,  // Asignar el idven de la venta creada
        ]);

        // Calcular el total de la venta sumando los montos de los detalles
        $totalVenta = $detalles->sum('montodet');

        // Asignar el total calculado a la venta
        $venta->totalpagoven = $totalVenta;

        // Guardar la venta con el total calculado
        $venta->save();

        // Relacionar los detalles con la venta
        foreach ($detalles as $detalle) {
            $venta->detalles_venta()->save($detalle);
        }

        // Devolver la venta como un array para la creación
        return $venta->toArray();
    }
    /**
     * Generar la venta con detalles de venta asociados.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withDetalles()
    {
        $cantidadDetalles = $this->faker->numberBetween(1, 7); // Genera entre 1 y 7 detalles aleatorios
        return $this->has(
            DetalleVenta::factory()->count($cantidadDetalles), // Genera detalles de venta con el número aleatorio
            'detalles_venta' // Relación con detalles_venta
        );
    }
}
