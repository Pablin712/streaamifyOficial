<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_mensajes_canal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idmsg')->nullable();
            $table->unsignedBigInteger('idconv')->nullable();
            $table->unsignedBigInteger('contacto_canal_id')->nullable();
            $table->enum('canal', ['whatsapp', 'messenger', 'telegram', 'webchat']);
            $table->enum('direccion', ['inbound', 'outbound', 'status']);
            $table->string('external_message_id', 191)->nullable();
            $table->string('external_thread_id', 191)->nullable();
            $table->enum('external_status', ['received', 'accepted', 'sent', 'delivered', 'read', 'failed', 'deleted'])->nullable();
            $table->string('media_id', 191)->nullable();
            $table->text('media_url')->nullable();
            $table->string('media_mime_type', 120)->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['canal', 'external_message_id'], 'chat_mensajes_canal_external_unique');
            $table->index(['idconv', 'direccion']);
            $table->index(['contacto_canal_id', 'created_at']);
            $table->index(['canal', 'external_status']);

            $table->foreign('idmsg')
                ->references('idmsg')
                ->on('mensajes')
                ->nullOnDelete();

            $table->foreign('idconv')
                ->references('idconv')
                ->on('conversaciones')
                ->nullOnDelete();

            $table->foreign('contacto_canal_id')
                ->references('id')
                ->on('chat_contactos_canal')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_mensajes_canal');
    }
};
