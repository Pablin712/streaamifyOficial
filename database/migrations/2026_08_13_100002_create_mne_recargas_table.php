<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mne_recargas', function (Blueprint $table) {
            $table->id();
            $table->string('operadora', 50);
            $table->string('cliente_nombre', 150)->nullable();
            $table->string('cliente_telefono', 20)->nullable();
            $table->decimal('valor_cobrado', 15, 2)->comment('Lo que paga el cliente');
            $table->decimal('costo_fondo', 18, 4)->comment('Lo que realmente consume el fondo');
            $table->decimal('ganancia', 18, 4)->comment('valor_cobrado - costo_fondo');

            // Fondo operativo que se consume (ej. "Mi Negocio Efectivo")
            $table->unsignedBigInteger('fondo_id');
            $table->unsignedBigInteger('fondo_transaccion_id')->nullable();

            // Donde entro el pago del cliente: un banco real O el fondo "Efectivo" (nunca ambos)
            $table->unsignedBigInteger('banco_id')->nullable();
            $table->unsignedBigInteger('banco_transaccion_id')->nullable();
            $table->unsignedBigInteger('fondo_cobro_id')->nullable();
            $table->unsignedBigInteger('fondo_cobro_transaccion_id')->nullable();

            $table->dateTime('fecha');
            $table->text('notas')->nullable();
            $table->boolean('anulada')->default(false);
            $table->timestamps();

            $table->foreign('fondo_id')->references('id')->on('fondos')->onDelete('restrict');
            $table->foreign('fondo_transaccion_id')->references('id')->on('fondo_transacciones')->onDelete('set null');
            $table->foreign('banco_id')->references('idban')->on('bancos')->onDelete('set null');
            $table->foreign('banco_transaccion_id')->references('id')->on('transacciones')->onDelete('set null');
            $table->foreign('fondo_cobro_id')->references('id')->on('fondos')->onDelete('set null');
            $table->foreign('fondo_cobro_transaccion_id')->references('id')->on('fondo_transacciones')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mne_recargas');
    }
};
