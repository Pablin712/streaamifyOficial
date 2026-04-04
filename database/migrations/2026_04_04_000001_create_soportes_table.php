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
        Schema::create('soportes', function (Blueprint $table) {
            $table->id('idsop');
            $table->unsignedBigInteger('idcli');
            $table->string('idcue', 50);
            $table->enum('tipo', ['sin suscripcion', 'contrasena incorrecta', 'muchos dispositivos', 'otro']);
            $table->text('descripcion');
            $table->text('solucion')->nullable();
            $table->enum('estado', ['pendiente', 'atendido'])->default('pendiente');
            $table->timestamps();

            $table->foreign('idcli')->references('idcli')->on('clientes')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('idcue')->references('idcue')->on('cuentas')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->index(['idcli', 'estado']);
            $table->index(['idcue', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soportes');
    }
};
