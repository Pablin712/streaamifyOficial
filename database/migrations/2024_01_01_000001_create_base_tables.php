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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id('idemp');
            $table->string('nombreemp', 50);
            $table->string('telefonoemp', 15)->nullable();
            $table->string('usuarioemp', 20);
            $table->string('foto_url', 255)->nullable();
            $table->string('passwordemp', 60);
            $table->string('email', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->id('idcli');
            $table->string('nombrecli', 50);
            $table->string('email', 255)->unique()->nullable();
            $table->string('password', 255)->nullable();
            $table->rememberToken();
            $table->string('telefonocli', 50)->nullable();
            $table->decimal('saldo', 15, 2)->default(0.00);
            $table->string('pais', 30)->default('Ecuador');
            $table->timestamps();
            $table->string('codigo_referidor', 50)->nullable();
            $table->unsignedInteger('referido_por')->nullable();
            $table->boolean('ya_compro')->default(0);
        });

        Schema::create('servicios', function (Blueprint $table) {
            $table->string('idser', 10)->primary();
            $table->string('nombreser', 20);
            $table->decimal('completoser', 5, 2)->nullable();
            $table->decimal('precioser', 5, 2)->nullable();
            $table->decimal('comboser', 5, 2)->nullable();
            $table->decimal('reventaser', 5, 2)->nullable();
            $table->decimal('revcompser', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('proveedores', function (Blueprint $table) {
            $table->id('idpro');
            $table->string('nombrepro', 20);
            $table->string('telefonopro', 15)->nullable();
            $table->timestamps();
            $table->boolean('activopro')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('servicios');
        Schema::dropIfExists('proveedores');
    }
};