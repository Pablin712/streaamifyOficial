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
        Schema::create('telegram_auth_sessions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chat_id')->unique()->comment('ID del chat de Telegram');
            $table->string('step', 50)->default('inicio')->comment('Paso actual del flujo: inicio, login_email, login_password, registro_nombre, etc.');
            $table->enum('proceso', ['login', 'registro'])->nullable()->comment('Tipo de proceso en curso');
            $table->text('datos')->nullable()->comment('Datos recolectados durante el proceso: email, nombre, telefono, etc.');
            $table->tinyInteger('intentos')->unsigned()->default(0)->comment('Número de intentos fallidos de login');
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index('chat_id');
            $table->index('step');
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
