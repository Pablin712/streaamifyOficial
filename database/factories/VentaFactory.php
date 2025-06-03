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
        // Crear la venta inicialmente con totalpagoven = 0
        $venta = new Venta();
        $venta->idemp = Empleado::inRandomOrder()->first()->idemp;
        $venta->idcli = Cliente::inRandomOrder()->first()->idcli;
        $venta->fechaven = $this->faker->dateTimeBetween('2025-05-01', '2025-06-02');
        $venta->totalpagoven = 0; // Asignar un usuario por defecto// Asociar el mismo valor
        $venta->save();

        //dd($venta);
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
