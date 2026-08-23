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
        Schema::create('ventas', function (Blueprint $table) {
            $table->string('idven', 20)->primary();
            $table->unsignedBigInteger('idemp');
            $table->unsignedBigInteger('idcli');
            $table->date('fechaven');
            $table->decimal('totalpagoven', 8, 2)->nullable();
            $table->timestamps();

            $table->foreign('idemp')->references('idemp')->on('empleados');
            $table->foreign('idcli')->references('idcli')->on('clientes');
        });

        Schema::create('detalles_venta', function (Blueprint $table) {
            $table->id('iddet');
            $table->string('idven', 20);
            $table->string('idper', 50)->nullable();
            $table->string('descripciondet', 60)->nullable();
            $table->date('fechavendet');
            $table->decimal('montodet', 8, 2);
            $table->boolean('activodet');
            $table->timestamps();

            $table->foreign('idven')->references('idven')->on('ventas');
            $table->foreign('idper')->references('idper')->on('perfiles')
                  ->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('secuencia_factura', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('fecha')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_venta');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('secuencia_factura');
    }
};
