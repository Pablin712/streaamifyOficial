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
            $table->string('idven', 20)->primary(); // Cambiado a idven
            $table->unsignedBigInteger('idemp'); // Cambiado a idemp
            $table->unsignedBigInteger('idcli'); // Cambiado a idcli
            $table->date('fechaven')->default(DB::raw('CURRENT_DATE')); // Cambiado a fechaven
            $table->decimal('totalpagoven', 8, 2)->nullable(); // Cambiado a totalpagoven
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->foreign('idemp')
                ->references('idemp')
                ->on('empleados')
                ->onUpdate('restrict')
                ->onDelete('restrict'); // Relación con la tabla `empleados`

            $table->foreign('idcli')
                ->references('idcli')
                ->on('clientes')
                ->onUpdate('restrict')
                ->onDelete('restrict'); // Relación con la tabla `clientes`
        });

        Schema::create('detalles_venta', function (Blueprint $table) {
            $table->id('iddet'); // Cambiado a iddet
            $table->string('idven', 20); // Cambiado a idven
            $table->string('idcue', 20)->nullable(); // Cambiado a idcue
            $table->integer('perdet')->nullable(); // Cambiado a perdet
            $table->date('fechavendet'); // Cambiado a fechavendet
            $table->decimal('montodet', 8, 2); // Cambiado a montodet
            $table->boolean('activodet'); // Cambiado a activodet
        });

        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->foreign('idven')
                ->references('idven')
                ->on('ventas')
                ->onUpdate('restrict')
                ->onDelete('restrict'); // Relación con la tabla `ventas`

            $table->foreign('idcue')
                ->references('idcue')
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
            $table->dropForeign(['idemp']); // Eliminar relación foránea
            $table->dropForeign(['idcli']); // Eliminar relación foránea
        });
        Schema::dropIfExists('ventas');

        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->dropForeign(['idven']); // Eliminar relación foránea
            $table->dropForeign(['idcue']); // Eliminar relación foránea
        });
        Schema::dropIfExists('detalles_venta');
    }
};
