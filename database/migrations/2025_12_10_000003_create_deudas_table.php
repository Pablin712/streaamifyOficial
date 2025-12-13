<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('deudas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proveedor_id');
            $table->decimal('monto', 15, 2)->comment('Monto acumulado de la deuda');
            $table->decimal('monto_pagado', 15, 2)->default(0)->comment('Total abonado');
            $table->enum('estado', ['pendiente', 'pagada'])->default('pendiente');
            $table->timestamps();

            $table->foreign('proveedor_id')->references('idpro')->on('proveedores')->onDelete('cascade');
        });
    }
    public function down() {
        Schema::dropIfExists('deudas');
    }
};
