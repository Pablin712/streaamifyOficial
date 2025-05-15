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
        Schema::table('valores', function (Blueprint $table) {
            $table->enum('tipoval', ['completo', 'individual', 'hibrido'])->default('completo')->after('costoval')->comment('Tipo de valor: completo, individual o híbrido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('valores', function (Blueprint $table) {
            $table->dropColumn('tipoval');
        });
    }
};
