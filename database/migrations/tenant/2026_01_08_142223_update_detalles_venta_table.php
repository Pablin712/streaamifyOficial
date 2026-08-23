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
        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->enum('estado', ['COBRADO', 'PENDIENTE'])->default('PENDIENTE')->after('activodet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
