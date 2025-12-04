<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Empleado;
use App\Models\ApiKey;

class ApiKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar empleado "Laravel" por nombre
        $empleado = Empleado::where('nombreemp', 'Laravel')->first();

        if (!$empleado) {
            $this->command->error('❌ Error: No se encontró el empleado "Laravel"');
            $this->command->warn('Por favor, crea un empleado con nombreemp="Laravel" primero.');
            return;
        }

        // Eliminar API Keys existentes del empleado Laravel (si existen)
        ApiKey::where('empleado_id', $empleado->idemp)->delete();

        // Crear nueva API Key para el empleado Laravel
        $apiKey = ApiKey::generate(
            'API de Desarrollo - Empleado Laravel',
            $empleado->idemp
        );

        $this->command->info('✅ API Key creada exitosamente!');
        $this->command->line('═══════════════════════════════════════════════════════════');
        $this->command->line('📝 Nombre: ' . $apiKey->name);
        $this->command->line('🔑 API Key: ' . $apiKey->key);
        $this->command->line('👤 Empleado: ' . $empleado->nombreemp . ' (ID: ' . $empleado->idemp . ')');
        $this->command->line('📧 Email: ' . ($empleado->email ?? 'N/A'));
        $this->command->line('═══════════════════════════════════════════════════════════');
        $this->command->line('');
        $this->command->line('📌 Uso en Postman:');
        $this->command->line('Header: X-API-Key');
        $this->command->line('Value: ' . $apiKey->key);
        $this->command->line('');
        $this->command->line('📌 Ejemplo cURL:');
        $this->command->line('curl -H "X-API-Key: ' . $apiKey->key . '" http://localhost/api/v1/clientes');
    }
}
