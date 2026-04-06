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
        Schema::create('codigos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 6)->nullable();
            $table->text('mensaje')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->unsignedBigInteger('idcli')->nullable();
            $table->string('idcue', 50)->nullable();
            $table->string('usuariocue', 191)->nullable();
            $table->string('idser', 20)->nullable();
            $table->string('instance', 50)->nullable();
            $table->string('apikey', 50)->nullable();
            $table->unsignedInteger('usuarios_habilitados')->default(0);
            $table->enum('estado', ['esperando', 'enviado'])->default('esperando');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['telefono', 'usuariocue', 'idser', 'estado', 'created_at'], 'codigos_lookup_idx');
            $table->index(['idcli', 'created_at'], 'codigos_cliente_idx');
            $table->index(['idcue', 'created_at'], 'codigos_cuenta_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codigos');
    }
};
