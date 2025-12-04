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
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id('idmsg');
            $table->unsignedBigInteger('idconv'); // Conversación a la que pertenece
            $table->enum('tipo_remitente', ['cliente', 'empleado', 'sistema', 'ia']); // Quién envió
            $table->unsignedBigInteger('idcli')->nullable(); // Si es cliente
            $table->unsignedBigInteger('idemp')->nullable(); // Si es empleado
            $table->text('contenido'); // Texto del mensaje
            $table->enum('tipo_contenido', ['texto', 'imagen', 'archivo', 'audio', 'sistema'])
                  ->default('texto');
            $table->string('archivo_url')->nullable(); // Si es archivo/imagen
            $table->boolean('leido')->default(false); // Para cliente
            $table->timestamp('leido_at')->nullable();
            $table->json('metadata')->nullable(); // IA confidence, sentiment, etc.
            $table->timestamps();

            // Foreign keys
            $table->foreign('idconv')
                  ->references('idconv')
                  ->on('conversaciones')
                  ->onDelete('cascade');

            $table->foreign('idcli')
                  ->references('idcli')
                  ->on('clientes')
                  ->onDelete('set null');

            $table->foreign('idemp')
                  ->references('idemp')
                  ->on('empleados')
                  ->onDelete('set null');

            // Índices
            $table->index('idconv');
            $table->index(['tipo_remitente', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};
