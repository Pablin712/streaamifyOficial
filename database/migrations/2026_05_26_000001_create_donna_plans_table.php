<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donna_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->enum('service_type', ['personal', 'business']);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('billing_cycle', ['monthly', 'yearly', 'one_time'])->default('monthly');
            $table->json('features_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donna_plans');
    }
};
