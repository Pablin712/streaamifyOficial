<?php

namespace Database\Factories;
use App\Models\Mantenimiento;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Cuenta;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mantenimiento>
 */
class MantenimientoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idcue' => Cuenta::inRandomOrder()->first()->idcue,  // Valores para idtip, según tu estructura
            'fechaman' => $this->faker->date('Y-m-d', '2025-01-31'),  // Fecha aleatoria dentro de 2024
            'descripcionman' => $this->faker->word() 
        ];
    }
}
