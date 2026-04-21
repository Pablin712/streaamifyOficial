<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

// Crear usuario de prueba para chat
$user = User::firstOrCreate(
    ['email' => 'chat_operator@test.com'],
    [
        'name' => 'Chat Operator',
        'password' => bcrypt('password123'),
        'email_verified_at' => now(),
    ]
);

// Asignar rol de Soporte (tiene chat.ver, chat.responder, chat.cerrar)
$role = Role::firstOrCreate(['name' => 'Soporte']);
$user->assignRole($role);

echo "✅ Usuario de prueba creado:\n";
echo "Email: chat_operator@test.com\n";
echo "Password: password123\n";
echo "Rol: Soporte\n";
echo "Permisos: chat.ver, chat.responder, chat.cerrar\n\n";

// También crear usuario supervisor
$supervisor = User::firstOrCreate(
    ['email' => 'chat_supervisor@test.com'],
    [
        'name' => 'Chat Supervisor',
        'password' => bcrypt('password123'),
        'email_verified_at' => now(),
    ]
);

$adminRole = Role::firstOrCreate(['name' => 'Administrador']);
$supervisor->assignRole($adminRole);

echo "✅ Supervisor creado:\n";
echo "Email: chat_supervisor@test.com\n";
echo "Password: password123\n";
echo "Rol: Administrador (todos los permisos)\n";