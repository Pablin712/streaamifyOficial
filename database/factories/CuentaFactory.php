<?php

namespace Database\Factories;
use App\Models\Cuenta;
use App\Models\Valor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cuenta>
 */
class CuentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Cuenta::class;
    public function definition(): array
    {
        $valor = Valor::inRandomOrder()->first();
        $servicio = $valor->idser;
        $numero = $this->faker->unique()->numberBetween(1, 100);

        return [
            'idcue' => $servicio . '-' . $numero,
            'idval' => $valor->idval,
            'fechavencue' => now()->addDays($this->faker->numberBetween(7, 45))->toDateString(),
            'usuariocue' => Str::lower($servicio) . $numero,
            'contrasenacue' => $this->faker->regexify('[A-Za-z0-9@#%]{10,14}'),
            'caidacue' => false,
            'activocue' => true,
        ];
    }
}
