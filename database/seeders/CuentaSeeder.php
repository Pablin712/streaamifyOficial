<?php

namespace Database\Seeders;

use App\Models\Cuenta;
use App\Models\Valor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CuentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceCounters = [];

        Valor::query()->orderBy('idser')->orderBy('idval')->get()->each(function (Valor $valor) use (&$serviceCounters) {
            // Cuentas completas: 3 por valor para simular escenarios (disponibles/saturadas/dañadas)
            // Cuentas individuales: 1 por valor
            $cantidadCuentas = $valor->tipoval === 'individual' ? 1 : 3;

            for ($index = 1; $index <= $cantidadCuentas; $index++) {
                $serviceCounters[$valor->idser] = ($serviceCounters[$valor->idser] ?? 0) + 1;
                $numero = str_pad((string) $serviceCounters[$valor->idser], 2, '0', STR_PAD_LEFT);
                $idcue = $valor->idser . '-' . $numero;

                // Marcar algunas cuentas como dañadas para pruebas operativas.
                // Solo aplica a cuentas completas y de forma determinística.
                $isDamaged = $valor->tipoval !== 'individual'
                    && $index === 3
                    && (abs(crc32($valor->idval)) % 4 === 0);

                Cuenta::updateOrCreate(
                    ['idcue' => $idcue],
                    [
                        'idval' => $valor->idval,
                        'fechavencue' => now()->addDays(fake()->numberBetween(10, 45))->toDateString(),
                        'usuariocue' => Str::lower($valor->idser) . $numero,
                        'contrasenacue' => fake()->regexify('[A-Za-z0-9@#%]{10,14}'),
                        'caidacue' => $isDamaged,
                        'activocue' => true,
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }
}
