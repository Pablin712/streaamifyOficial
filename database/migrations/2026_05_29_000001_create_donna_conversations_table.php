<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donna_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('channel_id');
            $table->string('external_chat_id', 100);        // remoteJid de WhatsApp
            $table->string('sender_identifier', 50)->nullable(); // número limpio: 593992571346
            $table->string('sender_name', 150)->nullable();
            $table->enum('status', ['open', 'closed', 'pending', 'human_takeover', 'blocked'])->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->string('last_message_preview', 255)->nullable();
            $table->text('memory_summary')->nullable(); // resumen de largo plazo opcional
            $table->unsignedBigInteger('assigned_to_user_id')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('idcli')->on('clientes')->onDelete('cascade');
            $table->foreign('channel_id')->references('id')->on('donna_channels')->onDelete('cascade');

            // Un remoteJid tiene una sola conversación activa por canal
            $table->unique(['channel_id', 'external_chat_id']);
            $table->index(['client_id', 'status']);
            $table->index('last_message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donna_conversations');
    }
};
