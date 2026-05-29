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
        if (!Schema::hasTable('donna_channels')) return;

        Schema::table('donna_channels', function (Blueprint $table) {
            if (!Schema::hasColumn('donna_channels', 'code_expires_at')) {
                $table->timestamp('code_expires_at')->nullable()->after('activation_code');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('donna_channels')) return;

        Schema::table('donna_channels', function (Blueprint $table) {
            if (Schema::hasColumn('donna_channels', 'code_expires_at')) {
                $table->dropColumn('code_expires_at');
            }
        });
    }
};
