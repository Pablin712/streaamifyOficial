<?php

namespace Database\Seeders;
use App\Models\Costo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CostoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Costo::factory()->count(20)->create();  // Crea 10 registros de costos
    }
}
