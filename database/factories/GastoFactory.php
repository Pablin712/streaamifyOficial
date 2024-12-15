<?php

namespace Database\Factories;
use App\Models\Gasto;
use App\Models\TipoGasto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Gasto>
 */
class GastoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Gasto::class;
    public function definition(): array
    {
        return [
            'idtip' => TipoGasto::inRandomOrder()->first()->idtip, // Relación aleatoria con un tipo de gasto
            'fechagas' => $this->faker->dateTimeBetween('2024-12-12', '2025-01-13'),  // Fecha aleatoria dentro de este año
            'montogas' => $this->faker->randomFloat(2, 5, 200), // Monto aleatorio entre 5 y 200
            'descripciongas' => $this->faker->text(50), // Descripción aleatoria
        ];
    }
}
