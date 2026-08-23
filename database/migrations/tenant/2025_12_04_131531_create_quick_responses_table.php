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
        Schema::create('quick_responses', function (Blueprint $table) {
            $table->id();
            $table->string('comando', 50)->unique()->comment('Comando sin / (ej: bancos, precios)');
            $table->string('titulo', 200)->comment('Título de la respuesta rápida');
            $table->text('contenido')->comment('Contenido de la respuesta');
            $table->enum('tipo', ['empleado', 'cliente', 'ambos'])->default('empleado')->comment('Quién puede usar este comando');
            $table->boolean('activo')->default(true)->comment('Si está activo para uso');
            $table->integer('orden')->default(0)->comment('Orden de aparición en listados');
            $table->json('tags')->nullable()->comment('Etiquetas para búsqueda (ej: ["pago", "transferencia"])');
            $table->timestamps();

            // Índices para búsqueda rápida
            $table->index('tipo');
            $table->index('activo');
            $table->index(['tipo', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quick_responses');
    }
};
