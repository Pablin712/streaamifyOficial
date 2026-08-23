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
        Schema::create('bancos', function (Blueprint $table) {
            $table->id('idban');
            $table->string('nombreban', 100);
            $table->string('propietarioban', 150);
            $table->string('cedulaban', 20);
            $table->string('numeroban', 20);
            $table->string('tipoban', 50);
            $table->text('detalleban')->nullable();
            $table->string('foto', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('estado_recargas', function (Blueprint $table) {
            $table->id('idestado');
            $table->string('nombre', 50);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('recargas', function (Blueprint $table) {
            $table->id('idrec');
            $table->unsignedBigInteger('idcli');
            $table->string('numcomprobante', 50)->unique();
            $table->decimal('valor', 15, 2);
            $table->string('foto', 255)->nullable();
            $table->unsignedBigInteger('idestado');
            $table->unsignedBigInteger('idban');
            $table->timestamps();
            
            $table->foreign('idcli')->references('idcli')->on('clientes')->onDelete('cascade');
            $table->foreign('idestado')->references('idestado')->on('estado_recargas')->onDelete('cascade');
            $table->foreign('idban')->references('idban')->on('bancos')->onDelete('cascade');
        });

        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idcli');
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('idestado')->default(1);
            $table->timestamp('fechapedido')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('respuesta', 255);
            
            $table->foreign('idcli')->references('idcli')->on('clientes')->onDelete('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
            $table->foreign('idestado')->references('idestado')->on('estado_recargas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('recargas');
        Schema::dropIfExists('estado_recargas');
        Schema::dropIfExists('bancos');
    }
};