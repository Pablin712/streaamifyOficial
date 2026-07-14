<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mensaje_reacciones')) {
            Schema::create('mensaje_reacciones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('idmsg');
                $table->enum('autor_tipo', ['cliente', 'empleado']);
                $table->string('emoji', 20);
                $table->timestamps();

                $table->foreign('idmsg')
                    ->references('idmsg')
                    ->on('mensajes')
                    ->cascadeOnDelete();

                $table->unique(['idmsg', 'autor_tipo'], 'msg_reaccion_autor_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mensaje_reacciones');
    }
};
