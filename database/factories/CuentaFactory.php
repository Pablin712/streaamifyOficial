<?php

namespace Database\Factories;
use App\Models\Cuenta;
use App\Models\Valor;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        // Elegir un valor aleatorio para `idval` de la tabla `Valores`
        $valor = Valor::inRandomOrder()->first();

        // Obtener el servicio asociado al valor (campo `idser`)
        $servicio = $valor->idser;

        // Generar un número aleatorio entre 1 y 100 para el identificador
        $numero = $this->faker->unique()->numberBetween(1, 100);
        return [
            'idcue' => $servicio . '-' . $numero, // Formato 'SERVICIO-NUMERO'
            'idval' => Valor::inRandomOrder()->first()->idval, // Relación aleatoria con un valor
            'fechavencue' => $this->faker->dateTimeBetween('2024-12-16', '2025-02-13'), // Fecha de vencimiento aleatoria dentro de este año
            'usuariocue' => $this->faker->userName, // Nombre de usuario aleatorio
            'contrasenacue' => $this->faker->password, // Contraseña encriptada
            'caidacue' => $this->faker->boolean, // Campo adicional aleatorio
        ];
    }
}
