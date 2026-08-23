<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fondo_transacciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fondo_id');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->decimal('monto_anterior', 18, 4);
            $table->decimal('monto_transaccion', 18, 4);
            $table->decimal('monto_actualizado', 18, 4);
            $table->string('referencia')->nullable();
            $table->dateTime('fecha');
            $table->boolean('anulada')->default(false);
            $table->timestamps();

            $table->foreign('fondo_id')->references('id')->on('fondos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fondo_transacciones');
    }
};
