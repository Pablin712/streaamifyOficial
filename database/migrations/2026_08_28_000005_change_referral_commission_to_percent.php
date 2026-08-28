<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * La comisión del partner pasa de ser un monto fijo en $ a un porcentaje
     * de lo que el cliente referido realmente paga (tras su descuento) — así
     * escala correctamente entre planes mensuales y anuales.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE donna_referral_partners CHANGE commission_amount commission_percent DECIMAL(5,2) NOT NULL');
        DB::statement('ALTER TABLE donna_subscriptions CHANGE referral_commission_amount referral_commission_percent DECIMAL(5,2) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE donna_referral_partners CHANGE commission_percent commission_amount DECIMAL(8,2) NOT NULL');
        DB::statement('ALTER TABLE donna_subscriptions CHANGE referral_commission_percent referral_commission_amount DECIMAL(8,2) NULL');
    }
};
