<?php

namespace Database\Seeders;

use App\Models\Venta;
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

        Venta::factory()
            ->count(150) // Crea 400 ventas
            ->withDetalles() // Asocia un número aleatorio de detalles (entre 1 y 7) por cada venta
            ->create();
        /*
        DB::table('ventas')->insert([
            ['idven' => 'FAC001-25112024', 'idemp' => 2, 'idcli' => 14, 'fechaven' => '2024-11-25', 'totalpagoven' => null],
            ['idven' => 'FAC002-26112024', 'idemp' => 2, 'idcli' => 45, 'fechaven' => '2024-11-26', 'totalpagoven' => null],
            ['idven' => 'FAC003-27112024', 'idemp' => 1, 'idcli' => 25, 'fechaven' => '2024-11-27', 'totalpagoven' => null],
            ['idven' => 'FAC004-28112024', 'idemp' => 2, 'idcli' => 13, 'fechaven' => '2024-11-28', 'totalpagoven' => null],
            ['idven' => 'FAC005-29112024', 'idemp' => 2, 'idcli' => 40, 'fechaven' => '2024-11-29', 'totalpagoven' => null],
            ['idven' => 'FAC006-30112024', 'idemp' => 1, 'idcli' => 54, 'fechaven' => '2024-11-30', 'totalpagoven' => null],
            ['idven' => 'FAC007-01122024', 'idemp' => 2, 'idcli' => 2, 'fechaven' => '2024-12-01', 'totalpagoven' => null],
            ['idven' => 'FAC008-02122024', 'idemp' => 1, 'idcli' => 6, 'fechaven' => '2024-12-02', 'totalpagoven' => null],
            ['idven' => 'FAC009-03122024', 'idemp' => 2, 'idcli' => 55, 'fechaven' => '2024-12-03', 'totalpagoven' => null],
            ['idven' => 'FAC010-04122024', 'idemp' => 1, 'idcli' => 49, 'fechaven' => '2024-12-04', 'totalpagoven' => null],
            ['idven' => 'FAC011-05122024', 'idemp' => 2, 'idcli' => 20, 'fechaven' => '2024-12-05', 'totalpagoven' => null],
            ['idven' => 'FAC012-06122024', 'idemp' => 1, 'idcli' => 24, 'fechaven' => '2024-12-06', 'totalpagoven' => null],
            ['idven' => 'FAC013-07122024', 'idemp' => 1, 'idcli' => 25, 'fechaven' => '2024-12-07', 'totalpagoven' => null],
            ['idven' => 'FAC014-08122024', 'idemp' => 1, 'idcli' => 42, 'fechaven' => '2024-12-08', 'totalpagoven' => null],
            ['idven' => 'FAC015-09122024', 'idemp' => 2, 'idcli' => 23, 'fechaven' => '2024-12-09', 'totalpagoven' => null],
            ['idven' => 'FAC016-10122024', 'idemp' => 2, 'idcli' => 7, 'fechaven' => '2024-12-10', 'totalpagoven' => null],
            ['idven' => 'FAC017-11122024', 'idemp' => 2, 'idcli' => 24, 'fechaven' => '2024-12-11', 'totalpagoven' => null],
            ['idven' => 'FAC018-12122024', 'idemp' => 1, 'idcli' => 11, 'fechaven' => '2024-12-12', 'totalpagoven' => null],
            ['idven' => 'FAC019-13122024', 'idemp' => 2, 'idcli' => 43, 'fechaven' => '2024-12-13', 'totalpagoven' => null],
            ['idven' => 'FAC020-14122024', 'idemp' => 2, 'idcli' => 54, 'fechaven' => '2024-12-14', 'totalpagoven' => null],
            ['idven' => 'FAC021-15122024', 'idemp' => 1, 'idcli' => 59, 'fechaven' => '2024-12-15', 'totalpagoven' => null],
        ]);*/
    }
}
