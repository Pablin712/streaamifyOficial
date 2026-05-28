<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donna_integrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->string('integration_type', 50);    // google, openai, deepseek
            $table->string('name', 150)->nullable();   // email / display name
            $table->text('access_token_encrypted')->nullable();
            $table->text('refresh_token_encrypted')->nullable();
            $table->datetime('token_expires_at')->nullable();
            $table->json('scopes_json')->nullable();
            $table->enum('status', ['active', 'expired', 'revoked', 'error'])->default('active');
            $table->datetime('last_sync_at')->nullable();
            $table->text('last_error')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index('client_id');
            $table->index(['client_id', 'integration_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donna_integrations');
    }
};
