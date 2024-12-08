<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('empleados')->insert([
            [
                'nombreemp' => 'Pablo Jiménez',
                'telefonoemp' => '0961778319',
                'usuarioemp' => 'pablinmind',
                'passwordemp' => bcrypt('pablin712'),
                'idrol' => 'administrador'
            ],
            [
                'nombreemp' => 'Mateo Jiménez',
                'telefonoemp' => '0961702129',
                'usuarioemp' => 'mateo18lol',
                'passwordemp' => bcrypt('passAdmin'),
                'idrol' => 'administrador'
            ],
            [
                'nombreemp' => 'Ronaldo Jiménez',
                'telefonoemp' => '0961412826',
                'usuarioemp' => 'RonaldMacDonald',
                'passwordemp' => bcrypt('hamburguesa'),
                'idrol' => 'vendedor'
            ],
            [
                'nombreemp' => 'Francisco Jiménez',
                'telefonoemp' => '0961412826',
                'usuarioemp' => 'franCisco',
                'passwordemp' => bcrypt('francisco1'),
                'idrol' => 'contador'
            ],
            [
                'nombreemp' => 'Darío Jiménez',
                'telefonoemp' => '09867400522',
                'usuarioemp' => 'dariojp',
                'passwordemp' => bcrypt('Dar78apk@'),
                'idrol' => 'tecnico'
            ],
            [
                'nombreemp' => 'Yadira Elizalde',
                'telefonoemp' => '0999947287',
                'usuarioemp' => 'yadira',
                'passwordemp' => bcrypt('yadira45'),
                'idrol' => 'bodeguero'
            ],
            [
                'nombreemp' => 'Nohelia Taimal',
                'telefonoemp' => '0961412826',
                'usuarioemp' => 'bodeguero',
                'passwordemp' => bcrypt('bodeguerojunior'),
                'idrol' => 'bodeguero'
            ],
            [
                'nombreemp' => 'Isabella Salazar',
                'telefonoemp' => '0961412826',
                'usuarioemp' => 'tecnico',
                'passwordemp' => bcrypt('tecnicojunior'),
                'idrol' => 'tecnico'
            ],
        ]);
    }
}
