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
        if (!Schema::hasTable('donna_channels')) {
            return; // La migración anterior ya creó la tabla con todos los campos
        }

        Schema::table('donna_channels', function (Blueprint $table) {
            if (!Schema::hasColumn('donna_channels', 'telegram_username')) {
                $table->string('telegram_username', 100)->nullable()->after('owner_identifier');
            }
            if (!Schema::hasColumn('donna_channels', 'telegram_name')) {
                $table->string('telegram_name', 150)->nullable()->after('telegram_username');
            }
            if (!Schema::hasColumn('donna_channels', 'activated_at')) {
                $table->timestamp('activated_at')->nullable()->after('telegram_name');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('donna_channels')) return;

        Schema::table('donna_channels', function (Blueprint $table) {
            $cols = array_filter(
                ['telegram_username', 'telegram_name', 'activated_at'],
                fn ($c) => Schema::hasColumn('donna_channels', $c)
            );
            if ($cols) $table->dropColumn(array_values($cols));
        });
    }
};
