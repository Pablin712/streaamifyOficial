<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('transacciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('banco_id');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->decimal('monto_anterior', 15, 2);
            $table->decimal('monto_transaccion', 15, 2);
            $table->decimal('monto_actualizado', 15, 2);
            $table->string('referencia')->nullable();
            $table->dateTime('fecha');
            $table->timestamps();

            $table->foreign('banco_id')->references('idban')->on('bancos')->onDelete('cascade');
        });
    }
    public function down() {
        Schema::dropIfExists('transacciones');
    }
};
