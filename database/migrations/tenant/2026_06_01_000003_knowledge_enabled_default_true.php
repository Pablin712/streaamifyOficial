<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donna_agent_configs', function (Blueprint $table) {
            $table->boolean('knowledge_enabled')->default(true)->change();
        });
    }

    public function down(): void
    {
        Schema::table('donna_agent_configs', function (Blueprint $table) {
            $table->boolean('knowledge_enabled')->default(false)->change();
        });
    }
};
