<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contabilidad', function (Blueprint $table) {
            $table->id('idcon');  // idcon como clave primaria, autoincremental
            $table->integer('mes')->default(DB::raw('EXTRACT(MONTH FROM CURRENT_DATE)'));  // mes por defecto es el mes actual
            $table->integer('año')->default(DB::raw('EXTRACT(YEAR FROM CURRENT_DATE)'));  // año por defecto es el año actual
            $table->string('detalle',20);  // detalle de la transacción
            $table->integer('num_cuentas');  // número de cuentas involucradas
            $table->integer('num_usuarios');  // número de usuarios involucrados
            $table->decimal('ingresos', 15, 2);  // ingresos (con 2 decimales)
            $table->decimal('costos', 15, 2);  // costos (con 2 decimales)
            $table->decimal('ganancias', 15, 2);  // ganancias (con 2 decimales)
            $table->decimal('renta', 5, 2);  // renta (con 2 decimales)
            $table->integer('num_ventas');
            
            // Para agregar índices o relaciones, si es necesario
            // $table->timestamps();  // Si deseas incluir las marcas de tiempo created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contabilidad');
    }
};
