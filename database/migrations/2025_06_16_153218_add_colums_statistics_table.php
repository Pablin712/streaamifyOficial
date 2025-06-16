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
        Schema::table('daily_statistics', function (Blueprint $table) {
            $table->integer('affected_customers')->default(0)->after('active_users');
            $table->integer('pending_payments')->default(0)->after('affected_customers');
            $table->integer('danger_accounts')->default(0)->after('pending_payments');
            $table->integer('accounts')->default(0)->after('danger_accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_statistics', function (Blueprint $table) {
            $table->dropColumn(['affected_customers', 'pending_payments', 'danger_accounts', 'accounts']);
        });
    }
};
