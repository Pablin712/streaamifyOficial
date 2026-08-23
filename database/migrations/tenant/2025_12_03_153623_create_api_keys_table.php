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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre descriptivo (ej: "App Móvil iOS")
            $table->string('key', 64)->unique(); // API Key única
            $table->unsignedBigInteger('empleado_id')->nullable(); // Empleado propietario
            $table->json('permissions')->nullable(); // Permisos específicos
            $table->timestamp('last_used_at')->nullable(); // Última vez usada
            $table->timestamp('expires_at')->nullable(); // Fecha de expiración
            $table->boolean('is_active')->default(true); // Activa/Inactiva
            $table->string('ip_whitelist')->nullable(); // IPs permitidas (opcional)
            $table->integer('requests_count')->default(0); // Contador de peticiones
            $table->timestamps();

            $table->foreign('empleado_id')
                  ->references('idemp')
                  ->on('empleados')
                  ->onDelete('cascade');

            // Índices para optimizar búsquedas
            $table->index('key');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
