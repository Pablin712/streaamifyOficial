<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ValorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('valores')->insert([
            ['idval' => 'NETFLIX-JUAN', 'idser' => 'NETFLIX', 'idpro' => 1, 'costoval' => 8, 'pantminval' => 4, 'pantmaxval' => 7, 'mesesval' => 1],
            ['idval' => 'DISNEYP-JUAN', 'idser' => 'DISNEYP', 'idpro' => 1, 'costoval' => 6, 'pantminval' => 4, 'pantmaxval' => 7, 'mesesval' => 1],
            ['idval' => 'DISNEYS-JUAN', 'idser' => 'DISNEYS', 'idpro' => 1, 'costoval' => 6, 'pantminval' => 4, 'pantmaxval' => 7, 'mesesval' => 1],
            ['idval' => 'MAX-JUAN', 'idser' => 'MAX', 'idpro' => 1, 'costoval' => 2.5, 'pantminval' => 3, 'pantmaxval' => 5, 'mesesval' => 1],
            ['idval' => 'PARAMOUNT-JUAN', 'idser' => 'PARAMOUNT', 'idpro' => 1, 'costoval' => 2.5, 'pantminval' => 3, 'pantmaxval' => 7, 'mesesval' => 1],
            ['idval' => 'CRUNCHY-JUAN', 'idser' => 'CRUNCHY', 'idpro' => 1, 'costoval' => 2.5, 'pantminval' => 4, 'pantmaxval' => 8, 'mesesval' => 1],
        ]);
    }
}
