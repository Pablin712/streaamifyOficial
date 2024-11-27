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
            $table->id('idemp'); // Cambiado a idemp
            $table->string('nombreemp', 20); // Cambiado a nombreemp
            $table->string('telefonoemp', 15)->nullable(); // Cambiado a telefonoemp
            $table->string('usuarioemp',20); //Usuario y Contraseña para Login
            $table->string('passwordemp',60);
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
