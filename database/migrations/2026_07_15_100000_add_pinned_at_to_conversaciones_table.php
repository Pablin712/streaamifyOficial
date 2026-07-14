<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('conversaciones', 'pinned_at')) {
                $table->timestamp('pinned_at')->nullable()->after('closed_at');
            }
        });

        Schema::table('conversaciones', function (Blueprint $table) {
            if (Schema::hasColumn('conversaciones', 'pinned_at')) {
                $table->index('pinned_at', 'chat_conv_pinned_at_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversaciones', function (Blueprint $table) {
            if (Schema::hasColumn('conversaciones', 'pinned_at')) {
                $table->dropIndex('chat_conv_pinned_at_idx');
                $table->dropColumn('pinned_at');
            }
        });
    }
};
