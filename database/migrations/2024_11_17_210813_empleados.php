<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear tabla roles
        Schema::create('roles', function (Blueprint $table) {
            $table->string('idrol', 20)->primary(); // Clave primaria
            $table->string('detallerol', 50);
            $table->timestamps(); // Opcional, elimina si no necesitas las columnas created_at y updated_at
        });
        Schema::create('empleados', function (Blueprint $table) {
            $table->id('idemp'); // Clave primaria idemp
            $table->string('nombreemp', 20); // Nombre del empleado
            $table->string('telefonoemp', 15)->nullable(); // Teléfono opcional
            $table->string('usuarioemp', 20); // Usuario para login
            $table->string('passwordemp', 60); // Contraseña para login
            $table->string('idrol', 20); // Rol asignado al empleado
            $table->timestamps(); // Incluye columnas created_at y updated_at
        
            // Llave foránea a roles.idrol
            $table->foreign('idrol')->references('idrol')->on('roles')->onDelete('cascade');
        });

        // Crear tabla permisos
        Schema::create('permisos', function (Blueprint $table) {
            $table->id('idperm'); // Serial primary key
            $table->string('idrol', 20);
            $table->string('name_table', 50);
            $table->string('accion', 50);
            $table->boolean('allowed');
            $table->timestamps(); // Opcional, elimina si no necesitas las columnas created_at y updated_at

            // Llave foránea
            $table->foreign('idrol')->references('idrol')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
        Schema::dropIfExists('empleados');
        Schema::dropIfExists('permisos');
    }
};
