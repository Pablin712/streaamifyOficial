<?php

namespace Database\Factories;
use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Perfil;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DetalleVenta>
 */
class DetalleVentaFactory extends Factory
{
    protected $model = DetalleVenta::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idven' => Venta::inRandomOrder()->first()->idven, // Relación aleatoria con una venta
            'idper' => Perfil::inRandomOrder()->first()->idper, // Relación aleatoria con un perfil
            'fechavendet' => $this->faker->dateTimeBetween('2024-12-16', '2025-02-13'), // Fecha aleatoria dentro de este año
            'montodet' => $this->faker->randomFloat(2, 1.5, 10), // Monto aleatorio entre 1.5 y 10
            'activodet' => $this->faker->boolean, // Estado aleatorio (activo o inactivo)
        ];
    }
}
