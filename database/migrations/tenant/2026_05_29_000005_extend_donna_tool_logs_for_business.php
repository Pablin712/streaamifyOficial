<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donna_tool_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('conversation_id')->nullable()->after('channel_id');
            $table->unsignedBigInteger('message_id')->nullable()->after('conversation_id');
        });
    }

    public function down(): void
    {
        Schema::table('donna_tool_logs', function (Blueprint $table) {
            $table->dropColumn(['conversation_id', 'message_id']);
        });
    }
};
