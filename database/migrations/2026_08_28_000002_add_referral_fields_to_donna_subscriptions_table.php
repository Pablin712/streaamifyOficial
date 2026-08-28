<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donna_subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('referral_partner_id')->nullable()->after('activated_by');
            // Snapshot de los montos del partner al momento de contratar/aprobar — así un cambio
            // futuro en la configuración del partner no altera retroactivamente esta suscripción.
            $table->decimal('referral_discount_amount', 8, 2)->nullable()->after('referral_partner_id');
            $table->decimal('referral_commission_amount', 8, 2)->nullable()->after('referral_discount_amount');

            $table->index('referral_partner_id');
        });
    }

    public function down(): void
    {
        Schema::table('donna_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['referral_partner_id']);
            $table->dropColumn(['referral_partner_id', 'referral_discount_amount', 'referral_commission_amount']);
        });
    }
};
