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
        // agregar columnas para usuarios a cobrar, espacios, cliente mas facturado
        Schema::table('daily_statistics', function (Blueprint $table) {
            $table->integer('usuarios_a_cobrar')->default(0)->after('active_users');
            $table->integer('espacios')->default(0)->after('usuarios_a_cobrar');
            $table->string('cliente_mas_facturado')->default('')->after('espacios');
            $table->integer('total_customers')->default(0)->after('cliente_mas_facturado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // eliminar las columnas agregadas
        Schema::table('daily_statistics', function (Blueprint $table) {
            $table->dropColumn('usuarios_a_cobrar');
            $table->dropColumn('espacios');
            $table->dropColumn('cliente_mas_facturado');
            $table->dropColumn('total_customers');
        });
    }
};
