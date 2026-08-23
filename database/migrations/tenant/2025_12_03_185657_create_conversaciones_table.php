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
        Schema::create('conversaciones', function (Blueprint $table) {
            $table->id('idconv');
            $table->unsignedBigInteger('idcli'); // Cliente
            $table->enum('estado', ['abierta', 'en_atencion', 'en_espera', 'cerrada', 'bot_activo'])
                  ->default('abierta');
            $table->unsignedBigInteger('ultimo_idemp')->nullable(); // Último empleado que respondió
            $table->timestamp('ultima_actividad')->useCurrent();
            $table->integer('mensajes_no_leidos')->default(0); // Para empleados
            $table->boolean('requiere_humano')->default(false); // IA solicita escalación
            $table->json('metadata')->nullable(); // Info adicional (tema, prioridad, tags)
            $table->timestamps();

            // Foreign keys
            $table->foreign('idcli')
                  ->references('idcli')
                  ->on('clientes')
                  ->onDelete('cascade');

            $table->foreign('ultimo_idemp')
                  ->references('idemp')
                  ->on('empleados')
                  ->onDelete('set null');

            // Índices
            $table->index('estado');
            $table->index('ultima_actividad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversaciones');
    }
};
