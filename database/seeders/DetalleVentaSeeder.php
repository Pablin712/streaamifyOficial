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
            ['idven' => 'FAC006-07112024', 'idper' => 'DISNEY-1.1', 'fechavendet' => '2024-12-07', 'montodet' => 3.25, 'activodet' => true],
            ['idven' => 'FAC006-07112024', 'idper' => 'NETFLIX-1.1', 'fechavendet' => '2024-12-07', 'montodet' => 3, 'activodet' => true],
            ['idven' => 'FAC006-07112024', 'idper' => 'MAX-1.1', 'fechavendet' => '2024-12-07', 'montodet' => 2, 'activodet' => true],
            ['idven' => 'FAC007-07112024', 'idper' => 'DISNEY-1.2', 'fechavendet' => '2024-12-07', 'montodet' => 3.5, 'activodet' => true],
            ['idven' => 'FAC007-07112024', 'idper' => 'NETFLIX-1.2', 'fechavendet' => '2024-12-07', 'montodet' => 3.25, 'activodet' => true],
            ['idven' => 'FAC007-07112024', 'idper' => 'MAX-1.2', 'fechavendet' => '2024-12-07', 'montodet' => 2, 'activodet' => true],
            ['idven' => 'FAC008-07112024', 'idper' => 'DISNEY-1.3', 'fechavendet' => '2024-12-07', 'montodet' => 3, 'activodet' => true],
            ['idven' => 'FAC008-07112024', 'idper' => 'NETFLIX-1.3', 'fechavendet' => '2024-12-07', 'montodet' => 3, 'activodet' => true],
            ['idven' => 'FAC009-07112024', 'idper' => 'MAGIS-1.1', 'fechavendet' => '2024-12-07', 'montodet' => 3, 'activodet' => true],
        ]);
    }
}
