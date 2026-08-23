<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deudor_id');
            $table->decimal('monto', 15, 2)->comment('Monto prestado');
            $table->decimal('monto_pagado', 15, 2)->default(0);
            $table->enum('estado', ['pendiente', 'pagado'])->default('pendiente');

            // Origen del desembolso: un banco real O el fondo "Efectivo" (nunca ambos)
            $table->unsignedBigInteger('banco_id')->nullable();
            $table->unsignedBigInteger('banco_transaccion_id')->nullable();
            $table->unsignedBigInteger('fondo_id')->nullable();
            $table->unsignedBigInteger('fondo_transaccion_id')->nullable();

            $table->dateTime('fecha');
            $table->string('motivo', 255)->nullable();
            $table->timestamps();

            $table->foreign('deudor_id')->references('id')->on('deudores')->onDelete('cascade');
            $table->foreign('banco_id')->references('idban')->on('bancos')->onDelete('set null');
            $table->foreign('banco_transaccion_id')->references('id')->on('transacciones')->onDelete('set null');
            $table->foreign('fondo_id')->references('id')->on('fondos')->onDelete('set null');
            $table->foreign('fondo_transaccion_id')->references('id')->on('fondo_transacciones')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
