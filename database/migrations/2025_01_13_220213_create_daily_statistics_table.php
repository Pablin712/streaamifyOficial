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
        Schema::create('daily_statistics', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique(); // Fecha del registro
            $table->integer('active_users'); // Número de usuarios activos
            $table->decimal('daily_revenue', 10, 2)->default(0); // Ingresos del día
            $table->decimal('daily_cost', 10, 2)->default(0); // Costos del día
            $table->decimal('daily_bill', 10, 2)->default(0); // Gastos del día
            $table->integer('daily_sales')->default(0); // Total de ventas del día
            $table->integer('new_customers')->default(0); // Nuevos clientes registrados
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_statistics');
    }
};
