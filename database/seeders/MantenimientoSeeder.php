<?php

namespace Database\Seeders;

use App\Models\Mantenimiento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MantenimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Mantenimiento::factory()->count(6)->create();
    }
}
