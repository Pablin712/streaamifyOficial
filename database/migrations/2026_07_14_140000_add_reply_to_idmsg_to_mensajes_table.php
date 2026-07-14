<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mensajes', function (Blueprint $table) {
            if (!Schema::hasColumn('mensajes', 'reply_to_idmsg')) {
                $table->unsignedBigInteger('reply_to_idmsg')->nullable()->after('external_id');
            }
        });

        Schema::table('mensajes', function (Blueprint $table) {
            if (Schema::hasColumn('mensajes', 'reply_to_idmsg')) {
                $table->index('reply_to_idmsg', 'chat_msg_reply_to_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mensajes', function (Blueprint $table) {
            if (Schema::hasColumn('mensajes', 'reply_to_idmsg')) {
                $table->dropIndex('chat_msg_reply_to_idx');
                $table->dropColumn('reply_to_idmsg');
            }
        });
    }
};
