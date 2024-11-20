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
        Schema::create('ventas', function (Blueprint $table) {
            $table->string('idventa', 20)->primary(); // Clave primaria
            $table->unsignedBigInteger('idempleado'); // Clave foránea hacia empleados
            $table->unsignedBigInteger('idcliente'); // Clave foránea hacia clientes
            $table->date('fechaventa')->default(DB::raw('CURRENT_DATE')); // Fecha con valor por defecto
            $table->decimal('totalpago', 8, 2)->nullable(); // Total del pago opcional
        });
        Schema::table('ventas', function (Blueprint $table) {
            $table->foreign('idempleado')
                ->references('idempleado')
                ->on('empleados')
                ->onUpdate('restrict')
                ->onDelete('restrict'); // Relación con la tabla `empleados`

            $table->foreign('idcliente')
                ->references('idcliente')
                ->on('clientes')
                ->onUpdate('restrict')
                ->onDelete('restrict'); // Relación con la tabla `clientes`
        });

        Schema::create('detalles_venta', function (Blueprint $table) {
            $table->id('idetalle'); // Clave primaria con autoincremento
            $table->string('idventa', 20); // Clave foránea hacia ventas
            $table->string('idcuenta', 20)->nullable(); // Clave foránea opcional hacia cuentas
            $table->integer('perfil')->nullable(); // Campo opcional
            $table->date('fechavenc'); // Fecha de vencimiento obligatoria
            $table->decimal('monto', 8, 2); // Monto decimal
            $table->boolean('activo'); // Campo booleano
        });

        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->foreign('idventa')
                ->references('idventa')
                ->on('ventas')
                ->onUpdate('restrict')
                ->onDelete('restrict'); // Relación con la tabla `ventas`

            $table->foreign('idcuenta')
                ->references('idcuenta')
                ->on('cuentas')
                ->onUpdate('restrict')
                ->onDelete('restrict'); // Relación con la tabla `cuentas`
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['idempleado']); // Eliminar relación foránea
            $table->dropForeign(['idcliente']); // Eliminar relación foránea
        });
        Schema::dropIfExists('ventas');
        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->dropForeign(['idventa']); // Eliminar relación foránea
            $table->dropForeign(['idcuenta']); // Eliminar relación foránea
        });
        Schema::dropIfExists('detalles_venta');
    }
};
