<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donna_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('referral_partner_id')->nullable()->after('plan_id');
            $table->index('referral_partner_id');
        });
    }

    public function down(): void
    {
        Schema::table('donna_requests', function (Blueprint $table) {
            $table->dropIndex(['referral_partner_id']);
            $table->dropColumn('referral_partner_id');
        });
    }
};
