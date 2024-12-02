<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetalleVentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('detalles_venta')->insert([
            // Primeras 5 ventas con fechas modificadas
            ['idven' => 'FAC001-25112024', 'idper' => 'NETFLIX-3.1', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-25', 'montodet' => 2.5, 'activodet' => true],
            ['idven' => 'FAC001-25112024', 'idper' => 'MAX-1.1', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-25', 'montodet' => 4.0, 'activodet' => true],

            ['idven' => 'FAC002-26112024', 'idper' => 'DISNEY-1.2', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-26', 'montodet' => 4.0, 'activodet' => true],
            ['idven' => 'FAC002-26112024', 'idper' => 'NETFLIX-2.2', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-26', 'montodet' => 3.2, 'activodet' => true],
            ['idven' => 'FAC002-26112024', 'idper' => 'MAGIS-3.2', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-26', 'montodet' => 2.8, 'activodet' => true],

            ['idven' => 'FAC003-27112024', 'idper' => 'NETFLIX-2.4', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-27', 'montodet' => 4.5, 'activodet' => true],
            ['idven' => 'FAC003-27112024', 'idper' => 'DISNEY-1.3', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-27', 'montodet' => 3.1, 'activodet' => true],
            ['idven' => 'FAC003-27112024', 'idper' => 'MAX-1.4', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-27', 'montodet' => 3.7, 'activodet' => true],

            ['idven' => 'FAC004-28112024', 'idper' => 'DISNEY-2.2', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-28', 'montodet' => 3.5, 'activodet' => true],
            ['idven' => 'FAC004-28112024', 'idper' => 'NETFLIX-1.3', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-28', 'montodet' => 2.8, 'activodet' => true],
            ['idven' => 'FAC004-28112024', 'idper' => 'CRUNCHYROLL-1.1', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-28', 'montodet' => 4.1, 'activodet' => true],

            ['idven' => 'FAC005-29112024', 'idper' => 'MAX-2.5', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-29', 'montodet' => 3.9, 'activodet' => true],
            ['idven' => 'FAC005-29112024', 'idper' => 'NETFLIX-5.2', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-29', 'montodet' => 3.0, 'activodet' => true],
            ['idven' => 'FAC005-29112024', 'idper' => 'MAGIS-1.1', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-29', 'montodet' => 2.6, 'activodet' => true],

            ['idven' => 'FAC006-30112024', 'idper' => 'NETFLIX-2.4', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-30', 'montodet' => 4.5, 'activodet' => true],
            ['idven' => 'FAC006-30112024', 'idper' => 'DISNEY-1.3', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-30', 'montodet' => 3.1, 'activodet' => true],
            ['idven' => 'FAC006-30112024', 'idper' => 'MAX-1.4', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-30', 'montodet' => 3.7, 'activodet' => true],

            ['idven' => 'FAC007-01122024', 'idper' => 'DISNEY-2.4', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-01', 'montodet' => 3.6, 'activodet' => true],
            ['idven' => 'FAC007-01122024', 'idper' => 'NETFLIX-1.5', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-01', 'montodet' => 2.9, 'activodet' => true],
            ['idven' => 'FAC007-01122024', 'idper' => 'CRUNCHYROLL-1.2', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-01', 'montodet' => 4.3, 'activodet' => true],

            ['idven' => 'FAC008-02122024', 'idper' => 'DISNEY-3.1', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-02', 'montodet' => 3.8, 'activodet' => true],
            ['idven' => 'FAC008-02122024', 'idper' => 'NETFLIX-3.2', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-02', 'montodet' => 3.3, 'activodet' => true],
            ['idven' => 'FAC008-02122024', 'idper' => 'MAX-2.5', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-02', 'montodet' => 4.0, 'activodet' => true],

            ['idven' => 'FAC009-03122024', 'idper' => 'DISNEY-1.4', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-03', 'montodet' => 3.4, 'activodet' => true],
            ['idven' => 'FAC009-03122024', 'idper' => 'NETFLIX-1.3', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-03', 'montodet' => 3.2, 'activodet' => true],
            ['idven' => 'FAC009-03122024', 'idper' => 'MAX-2.4', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-03', 'montodet' => 3.6, 'activodet' => true],

            ['idven' => 'FAC010-04122024', 'idper' => 'DISNEY-2.5', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-04', 'montodet' => 3.9, 'activodet' => true],
            ['idven' => 'FAC010-04122024', 'idper' => 'NETFLIX-3.5', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-04', 'montodet' => 2.8, 'activodet' => true],
            ['idven' => 'FAC010-04122024', 'idper' => 'MAGIS-1.3', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-04', 'montodet' => 3.0, 'activodet' => true],

            ['idven' => 'FAC011-05122024', 'idper' => 'DISNEY-1.6', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-01', 'montodet' => 4.2, 'activodet' => true],
            ['idven' => 'FAC011-05122024', 'idper' => 'NETFLIX-2.1', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-01', 'montodet' => 3.4, 'activodet' => true],
            ['idven' => 'FAC011-05122024', 'idper' => 'MAX-1.1', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-01', 'montodet' => 3.9, 'activodet' => true],

            ['idven' => 'FAC012-06122024', 'idper' => 'DISNEY-3.1', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-02', 'montodet' => 4.0, 'activodet' => true],
            ['idven' => 'FAC012-06122024', 'idper' => 'NETFLIX-2.3', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-02', 'montodet' => 3.2, 'activodet' => true],
            ['idven' => 'FAC012-06122024', 'idper' => 'MAX-2.1', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-02', 'montodet' => 4.1, 'activodet' => true],

            ['idven' => 'FAC013-07122024', 'idper' => 'DISNEY-2.7', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-03', 'montodet' => 4.3, 'activodet' => true],
            ['idven' => 'FAC013-07122024', 'idper' => 'NETFLIX-3.5', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-03', 'montodet' => 2.9, 'activodet' => true],
            ['idven' => 'FAC013-07122024', 'idper' => 'MAX-2.3', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-03', 'montodet' => 3.8, 'activodet' => true],

            ['idven' => 'FAC014-08122024', 'idper' => 'DISNEY-3.4', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-04', 'montodet' => 3.7, 'activodet' => true],
            ['idven' => 'FAC014-08122024', 'idper' => 'NETFLIX-3.4', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-04', 'montodet' => 2.8, 'activodet' => true],
            ['idven' => 'FAC014-08122024', 'idper' => 'MAX-2.4', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-04', 'montodet' => 4.0, 'activodet' => true],

            ['idven' => 'FAC015-09122024', 'idper' => 'DISNEY-1.7', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-05', 'montodet' => 3.5, 'activodet' => true],
            ['idven' => 'FAC015-09122024', 'idper' => 'NETFLIX-2.2', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-05', 'montodet' => 3.0, 'activodet' => true],
            ['idven' => 'FAC015-09122024', 'idper' => 'MAX-2.2', 'descripciondet' => 'vendido', 'fechavendet' => '2024-12-05', 'montodet' => 3.9, 'activodet' => true],
            ['idven' => 'FAC015-09122024', 'idper' => 'DISNEY-1.6', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-09', 'montodet' => 3.8, 'activodet' => true],
            ['idven' => 'FAC015-09122024', 'idper' => 'NETFLIX-2.4', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-09', 'montodet' => 4.0, 'activodet' => true],
            ['idven' => 'FAC015-09122024', 'idper' => 'MAX-3.3', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-09', 'montodet' => 4.2, 'activodet' => true],

            ['idven' => 'FAC016-10122024', 'idper' => 'DISNEY-4.1', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-10', 'montodet' => 3.7, 'activodet' => true],
            ['idven' => 'FAC016-10122024', 'idper' => 'NETFLIX-4.2', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-10', 'montodet' => 2.9, 'activodet' => true],
            ['idven' => 'FAC016-10122024', 'idper' => 'MAX-3.2', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-10', 'montodet' => 4.1, 'activodet' => true],

            ['idven' => 'FAC017-11122024', 'idper' => 'DISNEY-4.5', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-11', 'montodet' => 4.0, 'activodet' => true],
            ['idven' => 'FAC017-11122024', 'idper' => 'NETFLIX-4.3', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-11', 'montodet' => 3.5, 'activodet' => true],
            ['idven' => 'FAC017-11122024', 'idper' => 'MAX-3.5', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-11', 'montodet' => 4.3, 'activodet' => true],

            ['idven' => 'FAC018-12122024', 'idper' => 'DISNEY-5.5', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-12', 'montodet' => 3.9, 'activodet' => true],
            ['idven' => 'FAC018-12122024', 'idper' => 'NETFLIX-5.1', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-12', 'montodet' => 4.0, 'activodet' => true],
            ['idven' => 'FAC018-12122024', 'idper' => 'MAX-3.4', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-12', 'montodet' => 4.2, 'activodet' => true],

            ['idven' => 'FAC019-13122024', 'idper' => 'DISNEY-5.2', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-13', 'montodet' => 4.4, 'activodet' => true],
            ['idven' => 'FAC019-13122024', 'idper' => 'NETFLIX-5.4', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-13', 'montodet' => 3.3, 'activodet' => true],
            ['idven' => 'FAC019-13122024', 'idper' => 'MAX-4.1', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-13', 'montodet' => 3.8, 'activodet' => true],

            ['idven' => 'FAC020-14122024', 'idper' => 'DISNEY-5.5', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-14', 'montodet' => 3.6, 'activodet' => true],
            ['idven' => 'FAC020-14122024', 'idper' => 'NETFLIX-5.3', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-14', 'montodet' => 3.1, 'activodet' => true],
            ['idven' => 'FAC020-14122024', 'idper' => 'MAX-4.2', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-14', 'montodet' => 4.0, 'activodet' => true],

            ['idven' => 'FAC021-15122024', 'idper' => 'DISNEY-5.1', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-15', 'montodet' => 4.1, 'activodet' => true],
            ['idven' => 'FAC021-15122024', 'idper' => 'NETFLIX-6.1', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-15', 'montodet' => 3.5, 'activodet' => true],
            ['idven' => 'FAC021-15122024', 'idper' => 'MAX-4.5', 'descripciondet' => 'vendido', 'fechavendet' => '2025-01-15', 'montodet' => 4.2, 'activodet' => true],

        ]);
    }
}
