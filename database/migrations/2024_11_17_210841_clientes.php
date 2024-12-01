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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id('idcli'); // Cambiado a idcli
            $table->string('nombrecli', 50); // Cambiado a nombrecli
            $table->string('telefonocli', 15)->nullable(); // Cambiado a telefonocli
            $table->timestamps(); // Incluye columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
