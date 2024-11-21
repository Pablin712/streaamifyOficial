<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class VentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ventas')->insert([
            ['idven' => 'FAC006-07112024', 'idemp' => 1, 'idcli' => 1, 'fechaven' => '2024-11-07', 'totalpagoven' => null],
            ['idven' => 'FAC007-07112024', 'idemp' => 2, 'idcli' => 2, 'fechaven' => '2024-11-07', 'totalpagoven' => null],
            ['idven' => 'FAC008-07112024', 'idemp' => 1, 'idcli' => 3, 'fechaven' => '2024-11-07', 'totalpagoven' => null],
            ['idven' => 'FAC009-07112024', 'idemp' => 1, 'idcli' => 4, 'fechaven' => '2024-11-07', 'totalpagoven' => null],
            ['idven' => 'FAC010-07112024', 'idemp' => 2, 'idcli' => 5, 'fechaven' => '2024-11-07', 'totalpagoven' => null],
        ]);
    }
}
