<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donna_agent_configs', function (Blueprint $table) {
            $table->unsignedTinyInteger('wait_seconds')->default(10)->after('max_tool_calls');
        });
    }

    public function down(): void
    {
        Schema::table('donna_agent_configs', function (Blueprint $table) {
            $table->dropColumn('wait_seconds');
        });
    }
};
