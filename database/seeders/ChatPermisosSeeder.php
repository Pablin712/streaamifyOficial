<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ChatPermisosSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos de chat
        $permisos = [
            'chat.ver',
            'chat.responder',
            'chat.cerrar',
            'chat.transferir',
            'chat.supervisor',
            'chat.admin',
            'chat.ver_estadisticas',
        ];

        foreach ($permisos as $nombre) {
            Permission::firstOrCreate(['name' => $nombre]);
        }

        // Asignar permisos a roles
        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $admin->givePermissionTo([
            'chat.ver',
            'chat.responder',
            'chat.cerrar',
            'chat.transferir',
            'chat.supervisor',
            'chat.admin',
            'chat.ver_estadisticas',
        ]);

        $soporte = Role::firstOrCreate(['name' => 'Soporte']);
        $soporte->givePermissionTo([
            'chat.ver',
            'chat.responder',
            'chat.cerrar',
        ]);

        $vendedor = Role::firstOrCreate(['name' => 'Vendedor']);
        $vendedor->givePermissionTo([
            'chat.ver',
            'chat.responder',
        ]);

        $this->command->info('✅ Permisos de chat creados y asignados');
    }
}
