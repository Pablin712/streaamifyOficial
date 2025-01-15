<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use App\Models\DailyStatistic;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyStatistic>
 */
class DailyStatisticFactory extends Factory
{
    protected $model = DailyStatistic::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'), // Fecha aleatoria en los últimos 30 días
            'active_users' => $this->faker->numberBetween(50, 500),                      // Usuarios activos entre 50 y 500
            'daily_revenue' => $this->faker->randomFloat(2, 1000, 10000),               // Ingresos diarios entre 1000 y 10000
            'daily_cost' => $this->faker->randomFloat(2, 500, 8000),                    // Costos diarios entre 500 y 8000
            'daily_bill' => $this->faker->randomFloat(2, 100, 5000),                    // Facturas diarias entre 100 y 5000
            'daily_sales' => $this->faker->numberBetween(10, 100),                      // Ventas diarias entre 10 y 100
            'new_customers' => $this->faker->numberBetween(1, 50),                      // Nuevos clientes entre 1 y 50
        ];
    }
}
