<?php

namespace Database\Factories;
use App\Models\Costo;
use App\Models\Cuenta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Costo>
 */
class CostoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Costo::class;
    public function definition(): array
    {
        return [
            'idcue' => Cuenta::inRandomOrder()->first()->idcue, // Relación aleatoria con una cuenta
            'fechacos' => $this->faker->dateTimeBetween('2024-11-25', '2024-12-18'), // Fecha aleatoria dentro de este año
            'montocos' => $this->faker->randomFloat(2, 2, 9), // Monto aleatorio entre 2 y 10
            'descripcioncos' => $this->faker->text(50), // Descripción aleatoria
        ];
    }
}
