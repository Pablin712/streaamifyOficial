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
        Schema::table('historial', function (Blueprint $table) {
            $table->unsignedBigInteger('idcli')->nullable()->after('empleado_id');
            $table->unsignedBigInteger('iddet')->nullable()->after('idcli');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historial', function (Blueprint $table) {
            $table->dropColumn(['idcli', 'iddet']);
        });
    }
};
