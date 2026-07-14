<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chat_etiquetas')) {
            Schema::create('chat_etiquetas', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 30)->unique();
                $table->string('color', 20);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('chat_conversacion_etiqueta')) {
            Schema::create('chat_conversacion_etiqueta', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversacion_id');
                $table->unsignedBigInteger('etiqueta_id');
                $table->timestamp('created_at')->nullable();

                $table->foreign('conversacion_id')
                    ->references('idconv')
                    ->on('conversaciones')
                    ->cascadeOnDelete();

                $table->foreign('etiqueta_id')
                    ->references('id')
                    ->on('chat_etiquetas')
                    ->cascadeOnDelete();

                $table->unique(['conversacion_id', 'etiqueta_id'], 'chat_conv_etiqueta_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversacion_etiqueta');
        Schema::dropIfExists('chat_etiquetas');
    }
};
