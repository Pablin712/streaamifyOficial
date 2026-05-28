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
        Schema::create('donna_tool_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->unsignedBigInteger('channel_id')->nullable();
            $table->string('tool_name', 100);
            $table->json('request_json')->nullable();
            $table->json('response_json')->nullable();
            $table->boolean('success')->default(false);
            $table->string('reason', 100)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'tool_name']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donna_tool_logs');
    }
};
