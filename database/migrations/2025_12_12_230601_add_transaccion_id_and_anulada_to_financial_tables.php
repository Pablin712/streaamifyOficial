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
        // Agregar campo anulada a transacciones
        Schema::table('transacciones', function (Blueprint $table) {
            $table->boolean('anulada')->default(false)->after('fecha');
        });

        // Agregar transaccion_id a ventas (SIN banco_id, usaremos transacciones->banco_id)
        Schema::table('ventas', function (Blueprint $table) {
            $table->unsignedBigInteger('transaccion_id')->nullable()->after('totalpagoven');
            $table->foreign('transaccion_id')->references('id')->on('transacciones')->onDelete('set null');
        });

        // Agregar transaccion_id a costos
        Schema::table('costos', function (Blueprint $table) {
            $table->unsignedBigInteger('transaccion_id')->nullable()->after('descripcioncos');
            $table->foreign('transaccion_id')->references('id')->on('transacciones')->onDelete('set null');
        });

        // Agregar transaccion_id a gastos
        Schema::table('gastos', function (Blueprint $table) {
            $table->unsignedBigInteger('transaccion_id')->nullable()->after('descripciongas');
            $table->foreign('transaccion_id')->references('id')->on('transacciones')->onDelete('set null');
        });

        // Agregar transaccion_id a recargas
        Schema::table('recargas', function (Blueprint $table) {
            $table->unsignedBigInteger('transaccion_id')->nullable()->after('idban');
            $table->foreign('transaccion_id')->references('id')->on('transacciones')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['transaccion_id']);
            $table->dropColumn('transaccion_id');
        });

        Schema::table('costos', function (Blueprint $table) {
            $table->dropForeign(['transaccion_id']);
            $table->dropColumn('transaccion_id');
        });

        Schema::table('gastos', function (Blueprint $table) {
            $table->dropForeign(['transaccion_id']);
            $table->dropColumn('transaccion_id');
        });

        Schema::table('recargas', function (Blueprint $table) {
            $table->dropForeign(['transaccion_id']);
            $table->dropColumn('transaccion_id');
        });

        Schema::table('transacciones', function (Blueprint $table) {
            $table->dropColumn('anulada');
        });
    }
};
