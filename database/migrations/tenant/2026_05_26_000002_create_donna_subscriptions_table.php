<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donna_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('plan_id');
            $table->enum('service_type', ['personal', 'business']);
            $table->enum('status', ['pending', 'active', 'suspended', 'expired', 'cancelled'])->default('pending');
            $table->string('billing_cycle', 50)->nullable();
            $table->decimal('price_paid', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->datetime('starts_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->datetime('last_payment_at')->nullable();
            $table->date('next_payment_due')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->text('suspended_reason')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('activated_by')->nullable();
            $table->timestamps();

            $table->index('client_id');
            $table->index('plan_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donna_subscriptions');
    }
};
