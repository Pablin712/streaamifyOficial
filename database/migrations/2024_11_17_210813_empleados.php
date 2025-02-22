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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id('idemp'); // Clave primaria idemp
            $table->string('nombreemp', 50); // Nombre del empleado
            $table->string('telefonoemp', 15)->nullable(); // Teléfono opcional
            $table->string('usuarioemp', 20); // Usuario para login
            $table->string('foto_url')->nullable(); // Columna para la foto, opcional
            $table->string('passwordemp', 60); // Contraseña para login
            $table->string('email', 50)->nullable();
            $table->timestamps(); // Incluye columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
