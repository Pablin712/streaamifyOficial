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
            $table->string('idcue', 20)->primary(); // Cambiado a idcue
            $table->string('idval', 20)->nullable(); // Cambiado a idval
            $table->date('fechavencue'); // Cambiado a fechavencue
            $table->string('usuariocue', 50); // Cambiado a usuariocue
            $table->string('contrasenacue', 50); // Cambiado a contrasenacue
            $table->boolean('caidacue'); // Cambiado a caidacue
            $table->timestamps(); // Incluye columnas created_at y updated_at
            
            $table->foreign('idval')
                ->references('idval')
                ->on('valores')
                ->onUpdate('restrict')
                ->onDelete('restrict'); // Relación con la tabla `valores`
        });

        Schema::create('costos', function (Blueprint $table) {
            $table->id('idcos'); // Cambiado a idcos
            $table->string('idcue', 20); // Cambiado a idcue
            $table->date('fechacos')->default(DB::raw('CURRENT_DATE')); // Cambiado a fechacos
            $table->decimal('montocos', 8, 2); // Cambiado a montocos
            $table->string('descripcioncos', 50)->nullable(); // Cambiado a descripcioncos

            $table->foreign('idcue')
                ->references('idcue')
                ->on('cuentas')
                ->onUpdate('restrict')
                ->onDelete('restrict'); // Relación con la tabla `cuentas`
        });

        Schema::create('perfiles', function (Blueprint $table) {
            $table->string('idper', 20)->primary(); // Cambiado a idper
            $table->string('idcue', 20); // Cambiado a idcue
            $table->integer('numeroper'); // Cambiado a numeroper
            $table->string('pinper', 6)->nullable(); // Cambiado a pinper

            $table->foreign('idcue')
                ->references('idcue')
                ->on('cuentas')
                ->onUpdate('restrict')
                ->onDelete('restrict'); // Relación con la tabla `cuentas`
        });
        Schema::create('mantenimientos',function(Blueprint $table){
            $table->id('idman');
            $table->string('idcue',20)->unique();
            $table->string('descripcionman');
            $table->date('fechaman');

            $table->foreign('idcue')
                ->references('idcue')
                ->on('cuentas')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfiles');
        Schema::dropIfExists('costos');
        Schema::dropIfExists('mantenimientos');
        Schema::dropIfExists('cuentas');
    }
};
