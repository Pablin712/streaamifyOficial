<?php

namespace Database\Factories;
use App\Models\Venta;
use App\Models\Empleado;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DetalleVenta;
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
        return [
            'idemp' => Empleado::inRandomOrder()->first()->idemp, // Relación aleatoria con un empleado
            'idcli' => Cliente::inRandomOrder()->first()->idcli, // Relación aleatoria con un cliente
            'fechaven' => $this->faker->dateTimeBetween('2024-11-25', '2025-01-13'), // Fecha aleatoria dentro de este año
        ];
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
