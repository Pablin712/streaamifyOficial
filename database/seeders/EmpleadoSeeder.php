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
            ['nombreemp' => 'MATEO JIMÉNEZ',
             'telefonoemp' => '0961702129',
             'usuarioemp' => 'mateo18lol',
             'passwordemp' => bcrypt('passAdmin'),
             'idrol' => 'administrador'
            ],
            
             
            ['nombreemp' => 'RONALDO JIMÉNEZ', 
            'telefonoemp' => '0961412826',
            'usuarioemp' => 'RonaldMacDonald', 
            'passwordemp' => bcrypt('hamburguesa'),
            'idrol' => 'vendedor'
            ]
        ]);
    }
}
