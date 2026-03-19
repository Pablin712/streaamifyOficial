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
        Schema::create('tipo_gasto', function (Blueprint $table) {
            $table->id('idtip');
            $table->string('detalletip', 30);
            $table->timestamps();
        });

        Schema::create('gastos', function (Blueprint $table) {
            $table->id('idgas');
            $table->unsignedBigInteger('idtip');
            $table->date('fechagas');
            $table->decimal('montogas', 8, 2);
            $table->string('descripciongas', 50)->nullable();
            $table->timestamps();

            $table->foreign('idtip')->references('idtip')->on('tipo_gasto');
        });

        Schema::create('contabilidad', function (Blueprint $table) {
            $table->id('idcon');
            $table->integer('mes');
            $table->integer('año');
            $table->string('detalle', 20);
            $table->integer('num_cuentas');
            $table->integer('num_usuarios');
            $table->decimal('ingresos', 15, 2);
            $table->decimal('costos', 15, 2);
            $table->decimal('gastos', 15, 2);
            $table->decimal('ganancias', 15, 2);
            $table->decimal('renta', 5, 2);
            $table->integer('num_ventas');
        });

        Schema::create('daily_statistics', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->integer('active_users');
            $table->integer('affected_customers')->default(0);
            $table->integer('pending_payments')->default(0);
            $table->integer('danger_accounts')->default(0);
            $table->integer('accounts')->default(0);
            $table->decimal('daily_revenue', 10, 2)->default(0.00);
            $table->decimal('daily_cost', 10, 2)->default(0.00);
            $table->decimal('daily_bill', 10, 2)->default(0.00);
            $table->integer('daily_sales')->default(0);
            $table->integer('new_customers')->default(0);
            $table->timestamps();
        });

        Schema::create('ventas_diarias', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->unique();
            $table->integer('numero_venta')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas_diarias');
        Schema::dropIfExists('daily_statistics');
        Schema::dropIfExists('contabilidad');
        Schema::dropIfExists('gastos');
        Schema::dropIfExists('tipo_gasto');
    }
};
