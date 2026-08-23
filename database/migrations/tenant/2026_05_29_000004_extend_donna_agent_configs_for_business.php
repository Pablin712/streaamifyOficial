<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donna_agent_configs', function (Blueprint $table) {
            $table->text('sales_rules')->nullable()->after('business_logic');
            $table->text('support_rules')->nullable()->after('sales_rules');
            $table->boolean('knowledge_enabled')->default(false)->after('calendar_id');
            $table->boolean('calendar_enabled')->default(false)->after('knowledge_enabled');
            $table->boolean('sheets_enabled')->default(false)->after('calendar_enabled');
            $table->boolean('human_takeover_enabled')->default(true)->after('sheets_enabled');
            $table->unsignedTinyInteger('max_tool_calls')->default(8)->after('human_takeover_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('donna_agent_configs', function (Blueprint $table) {
            $table->dropColumn([
                'sales_rules', 'support_rules',
                'knowledge_enabled', 'calendar_enabled', 'sheets_enabled',
                'human_takeover_enabled', 'max_tool_calls',
            ]);
        });
    }
};
