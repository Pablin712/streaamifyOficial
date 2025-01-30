<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cliente::factory()->count(100)->create();  // Crea 200 registros de clientes
        DB::table('clientes')->insert([
            [
                'nombrecli' => 'Pablo Darío Jiménez Elizalde', 
                'telefonocli' => '+593 96 177 8319',
                'email' => 'pablojimenezelizalde@gmail.com',
                'password' => bcrypt('legopoli7P$'),
                'saldo' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
