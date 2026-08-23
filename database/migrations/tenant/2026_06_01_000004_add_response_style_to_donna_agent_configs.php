<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donna_agent_configs', function (Blueprint $table) {
            $table->enum('response_style', ['concise', 'moderate', 'detailed'])
                  ->default('concise')
                  ->after('wait_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('donna_agent_configs', function (Blueprint $table) {
            $table->dropColumn('response_style');
        });
    }
};
