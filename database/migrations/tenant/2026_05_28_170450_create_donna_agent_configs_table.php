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
        Schema::create('donna_agent_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->enum('service_type', ['personal', 'business'])->default('personal');
            $table->string('agent_name', 50)->default('Donna');
            $table->string('business_name', 150)->nullable();
            $table->text('business_description')->nullable();
            $table->text('personal_context')->nullable();
            $table->text('business_context')->nullable();
            $table->text('business_logic')->nullable();
            $table->text('main_prompt')->nullable();
            $table->text('fallback_prompt')->nullable();
            $table->text('welcome_message')->nullable();
            $table->text('out_of_hours_message')->nullable();
            $table->string('tone', 30)->default('profesional, amable y directa');
            $table->string('language', 5)->default('es');
            $table->string('timezone', 50)->default('America/Guayaquil');
            $table->json('working_hours_json')->nullable();
            $table->text('human_handoff_msg')->nullable();
            $table->string('spreadsheet_id', 255)->nullable();
            $table->string('spreadsheet_name', 100)->nullable();
            $table->string('calendar_id', 150)->default('primary');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('client_id')->references('idcli')->on('clientes')->onDelete('cascade');
            $table->foreign('subscription_id')->references('id')->on('donna_subscriptions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donna_agent_configs');
    }
};
