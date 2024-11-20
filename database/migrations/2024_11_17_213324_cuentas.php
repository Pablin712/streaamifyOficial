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
        Schema::create('cuentas', function (Blueprint $table) {
            $table->string('idcuenta', 20)->primary(); // Clave primaria
            $table->string('idvalor', 20)->nullable(); // Clave foránea opcional
            $table->date('fechavenc'); // Fecha obligatoria
            $table->string('usuario', 50); // Usuario
            $table->string('contrasena', 50); // Contraseña
            $table->boolean('caida'); // Campo booleano

            $table->foreign('idvalor')
                ->references('idvalor')
                ->on('valores')
                ->onUpdate('restrict')
                ->onDelete('restrict'); // Relación con la tabla `valores`
        });
        Schema::create('costos', function (Blueprint $table) {
            $table->id('idcosto'); // Clave primaria con autoincremento
            $table->string('idcuenta', 20); // Clave foránea
            $table->date('fechacosto')->default(DB::raw('CURRENT_DATE')); // Fecha con valor por defecto
            $table->decimal('monto', 8, 2); // Monto decimal
            $table->string('descripcion', 50)->nullable(); // Descripción opcional

            $table->foreign('idcuenta')
                ->references('idcuenta')
                ->on('cuentas')
                ->onUpdate('restrict')
                ->onDelete('restrict'); // Relación con la tabla `cuentas`
        });
        Schema::create('perfiles', function (Blueprint $table) {
            $table->string('idperfil', 20)->primary(); // Clave primaria
            $table->string('idcuenta', 20); // Clave foránea
            $table->integer('numero'); // Número obligatorio
            $table->string('pin', 6)->nullable(); // PIN opcional

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
        Schema::dropIfExists('cuentas');
        Schema::dropIfExists('perfiles');
        Schema::dropIfExists('costos');
    }
};
