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
            [
                'idval' => 'CRUNCHY-JUAN',
                'idser' => 'CRUNCHY',
                'idpro' => 1,
                'costoval' => 2.50,
                'pantminval' => 4,
                'pantmaxval' => 8,
                'mesesval' => 1,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'idval' => 'DISNEYP-JUAN',
                'idser' => 'DISNEYP',
                'idpro' => 1,
                'costoval' => 6.00,
                'pantminval' => 4,
                'pantmaxval' => 7,
                'mesesval' => 1,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'idval' => 'DISNEYS-JUAN',
                'idser' => 'DISNEYS',
                'idpro' => 1,
                'costoval' => 3.00,
                'pantminval' => 4,
                'pantmaxval' => 7,
                'mesesval' => 1,
                'created_at' => null,
                'updated_at' => '2024-12-19 03:23:20',
            ],
            [
                'idval' => 'MAX-JUAN',
                'idser' => 'MAX',
                'idpro' => 1,
                'costoval' => 2.50,
                'pantminval' => 3,
                'pantmaxval' => 5,
                'mesesval' => 1,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'idval' => 'NETFLIX-JUAN',
                'idser' => 'NETFLIX',
                'idpro' => 1,
                'costoval' => 8.00,
                'pantminval' => 4,
                'pantmaxval' => 7,
                'mesesval' => 1,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'idval' => 'PARAMOUNT-JUAN',
                'idser' => 'PARAMOUNT',
                'idpro' => 1,
                'costoval' => 2.50,
                'pantminval' => 3,
                'pantmaxval' => 9,
                'mesesval' => 1,
                'created_at' => null,
                'updated_at' => '2024-12-19 22:25:45',
            ],
            [
                'idval' => 'PRIME-VIRTUAL',
                'idser' => 'PRIME',
                'idpro' => 3,
                'costoval' => 2.50,
                'pantminval' => 4,
                'pantmaxval' => 9,
                'mesesval' => 1,
                'created_at' => '2024-12-19 22:22:18',
                'updated_at' => '2024-12-19 22:22:18',
            ],
            [
                'idval' => 'DISNEYP-VIRTUAL',
                'idser' => 'DISNEYP',
                'idpro' => 3,
                'costoval' => 7.50,
                'pantminval' => 4,
                'pantmaxval' => 7,
                'mesesval' => 1,
                'created_at' => '2024-12-19 22:29:27',
                'updated_at' => '2024-12-19 22:29:27',
            ],
            [
                'idval' => 'DISNEYP-MOORMIX',
                'idser' => 'DISNEYP',
                'idpro' => 2,
                'costoval' => 7.00,
                'pantminval' => 4,
                'pantmaxval' => 7,
                'mesesval' => 1,
                'created_at' => '2024-12-19 22:31:47',
                'updated_at' => '2024-12-19 22:31:47',
            ],
            [
                'idval' => 'PRIME-FENIX',
                'idser' => 'PRIME',
                'idpro' => 4,
                'costoval' => 2.00,
                'pantminval' => 4,
                'pantmaxval' => 7,
                'mesesval' => 1,
                'created_at' => '2024-12-19 22:32:49',
                'updated_at' => '2024-12-19 22:32:49',
            ],
            [
                'idval' => 'SPOTIFY-FENIX',
                'idser' => 'SPOTIFY',
                'idpro' => 4,
                'costoval' => 2.50,
                'pantminval' => 1,
                'pantmaxval' => 1,
                'mesesval' => 1,
                'created_at' => '2024-12-19 22:33:43',
                'updated_at' => '2024-12-19 22:33:43',
            ],
        ]);
    }
}
