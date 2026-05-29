<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donna_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('channel_id');
            $table->unsignedBigInteger('conversation_id');

            $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
            $table->enum('sender_type', ['final_customer', 'ai_agent', 'business_owner', 'system'])->default('final_customer');
            $table->enum('message_type', [
                'text', 'audio', 'image', 'video', 'document', 'sticker', 'location', 'contact', 'system'
            ])->default('text');

            $table->string('provider_message_id', 100)->nullable()->index(); // ID de Evo API
            $table->string('external_chat_id', 100)->nullable();
            $table->string('sender_identifier', 50)->nullable();
            $table->string('sender_name', 150)->nullable();

            // Contenido
            $table->text('content_text')->nullable();     // texto del mensaje / caption
            $table->text('transcription_text')->nullable(); // audio transcripto
            $table->text('ocr_text')->nullable();           // texto extraído de imagen
            $table->text('ai_response_text')->nullable();   // respuesta del agente (para outbound)

            // Media
            $table->string('media_url', 500)->nullable();
            $table->string('media_mime_type', 100)->nullable();
            $table->unsignedInteger('media_size')->nullable();

            // Estado y metadata
            $table->enum('processing_status', [
                'received', 'stored', 'processing', 'responded', 'failed', 'ignored', 'blocked_service_inactive'
            ])->default('stored');
            $table->string('blocked_reason', 100)->nullable();
            $table->json('raw_payload_json')->nullable();
            $table->json('metadata_json')->nullable();

            $table->timestamps();

            $table->foreign('client_id')->references('idcli')->on('clientes')->onDelete('cascade');
            $table->foreign('channel_id')->references('id')->on('donna_channels')->onDelete('cascade');
            $table->foreign('conversation_id')->references('id')->on('donna_conversations')->onDelete('cascade');

            $table->index(['client_id', 'conversation_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donna_messages');
    }
};
