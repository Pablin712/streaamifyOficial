<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Empleado;

class EmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empleado = Empleado::create([
            'nombreemp' => 'Pablo Jiménez',
            'telefonoemp' => '0961778319',
            'usuarioemp' => 'pablinmind',
            'passwordemp' => 'pablin712'
        ]);
        // Asignar el rol manualmente en model_has_roles
        DB::table('model_has_roles')->insert([
            'role_id' => 1, // Asegúrate de que este ID corresponda al rol 'Admin'
            'model_type' => 'App\Models\Empleado',
            'model_id' => $empleado->idemp,
        ]);
        DB::table('empleados')->insert([
            [
                'nombreemp' => 'Mateo Jiménez',
                'telefonoemp' => '0961702129',
                'usuarioemp' => 'mateo18lol',
                'passwordemp' => bcrypt('passAdmin')
            ],
            [
                'nombreemp' => 'Ronaldo Jiménez',
                'telefonoemp' => '0961412826',
                'usuarioemp' => 'RonaldMacDonald',
                'passwordemp' => bcrypt('hamburguesa')
            ],
            [
                'nombreemp' => 'Francisco Jiménez',
                'telefonoemp' => '0961412826',
                'usuarioemp' => 'franCisco',
                'passwordemp' => bcrypt('francisco1')
            ],
            [
                'nombreemp' => 'Darío Jiménez',
                'telefonoemp' => '09867400522',
                'usuarioemp' => 'dariojp',
                'passwordemp' => bcrypt('Dar78apk@')
            ],
            [
                'nombreemp' => 'Yadira Elizalde',
                'telefonoemp' => '0999947287',
                'usuarioemp' => 'yadira',
                'passwordemp' => bcrypt('yadira45')
            ],
            [
                'nombreemp' => 'Nohelia Taimal',
                'telefonoemp' => '0961412826',
                'usuarioemp' => 'bodeguero',
                'passwordemp' => bcrypt('bodeguerojunior')
            ],
            [
                'nombreemp' => 'Laravel',
                'telefonoemp' => '0961412826',
                'usuarioemp' => 'vendedodr',
                'passwordemp' => bcrypt('vendedorjunior')
            ],
        ]);
    }
}
