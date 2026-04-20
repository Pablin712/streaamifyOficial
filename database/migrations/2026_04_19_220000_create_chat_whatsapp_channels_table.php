<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_whatsapp_channels', function (Blueprint $table) {
            $table->id();
            $table->string('instance_name', 120)->unique();
            $table->string('display_name', 120)->nullable();
            $table->string('api_key', 191);
            $table->string('server_url', 191)->default('https://evoapi.abigailsoft.com');
            $table->enum('color', ['verde', 'azul', 'otro'])->default('otro');
            $table->boolean('is_active')->default(true);
            $table->boolean('outbound_enabled')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'outbound_enabled']);
            $table->index('color');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_whatsapp_channels');
    }
};
