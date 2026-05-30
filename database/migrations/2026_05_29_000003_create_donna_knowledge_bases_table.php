<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donna_knowledge_bases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('client_id')->references('idcli')->on('clientes')->onDelete('cascade');
            $table->index('client_id');
        });

        Schema::create('donna_knowledge_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('knowledge_base_id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->enum('type', [
                'text', 'faq', 'product', 'service', 'policy', 'url', 'table'
            ])->default('text');
            $table->longText('content_text')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->json('metadata_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('knowledge_base_id')->references('id')->on('donna_knowledge_bases')->onDelete('cascade');
            $table->index(['client_id', 'is_active']);
            $table->index(['knowledge_base_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donna_knowledge_items');
        Schema::dropIfExists('donna_knowledge_bases');
    }
};
