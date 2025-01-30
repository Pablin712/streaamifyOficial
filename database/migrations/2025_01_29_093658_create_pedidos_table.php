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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idcli');
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('idestado')->default(1); // 1 = Pendiente
            $table->timestamp('fechapedido')->useCurrent();
            $table->string('respuesta');

            $table->foreign('idcli')->references('idcli')->on('clientes')->onDelete('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
            $table->foreign('idestado')->references('idestado')->on('estado_recargas')->onDelete('restrict'); // Restricción para evitar eliminar estados en uso
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
