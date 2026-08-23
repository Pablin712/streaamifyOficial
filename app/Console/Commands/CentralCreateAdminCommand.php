<?php

namespace App\Console\Commands;

use App\Models\SuperAdmin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CentralCreateAdminCommand extends Command
{
    protected $signature = 'central:create-admin';

    protected $description = 'Crea una cuenta de super-admin para el panel central de Tenants';

    public function handle(): int
    {
        $nombre = $this->ask('Nombre');
        $email = $this->ask('Email');
        $password = $this->secret('Contraseña (min. 8 caracteres)');

        $validator = Validator::make(compact('nombre', 'email', 'password'), [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:central.super_admins,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        SuperAdmin::create(compact('nombre', 'email', 'password'));

        $this->info("Super-admin '{$email}' creado.");

        return self::SUCCESS;
    }
}
