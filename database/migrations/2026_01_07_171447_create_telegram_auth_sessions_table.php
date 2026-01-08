<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabla para almacenar el estado de las conversaciones de autenticación
     * de Telegram durante el proceso de login/registro
     */
    public function up(): void
    {
        Schema::create('telegram_auth_sessions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chat_id')->unique()->comment('ID del chat de Telegram');
            $table->string('step', 50)->default('inicio')->comment('Paso actual del flujo (inicio, login_email, registro_nombre, etc)');
            $table->string('proceso', 20)->nullable()->comment('Tipo de proceso en curso: login o registro');
            $table->text('ultimo_mensaje_bot')->nullable()->comment('Último mensaje enviado por el bot');
            $table->text('ultimo_mensaje_usuario')->required()->comment('Último mensaje recibido del usuario');
            $table->text('datos')->nullable()->comment('Datos temporales recolectados en formato JSON string');
            $table->unsignedTinyInteger('intentos')->default(0)->comment('Número de intentos fallidos');
            $table->timestamp('last_activity')->nullable()->comment('Última actividad del usuario');
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index('chat_id');
            $table->index('step');
            $table->index('last_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_auth_sessions');
    }
};
