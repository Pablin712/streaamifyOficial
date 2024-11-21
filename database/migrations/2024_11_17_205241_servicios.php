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
        Schema::create('servicios', function (Blueprint $table) {
            $table->string('idser', 10)->primary(); // Cambiado a idser
            $table->string('nombreser', 20); // Cambiado a nombreser
            $table->decimal('completoser', 5, 2)->nullable(); // Cambiado a completoser
            $table->decimal('precioser', 5, 2)->nullable(); // Cambiado a precioser
            $table->decimal('comboser', 5, 2)->nullable(); // Cambiado a comboser
            $table->decimal('reventaser', 5, 2)->nullable(); // Cambiado a reventaser
            $table->decimal('revcompser', 5, 2)->nullable(); // Cambiado a revcompser
            $table->timestamps(); // Optional if needed
        });

        Schema::create('proveedores', function (Blueprint $table) {
            $table->id('idpro'); // Cambiado a idpro
            $table->string('nombrepro', 20); // Cambiado a nombrepro
            $table->string('telefonopro', 15)->nullable(); // Cambiado a telefonopro
            $table->timestamps(); // Optional if needed
        });

        Schema::create('valores', function (Blueprint $table) {
            $table->string('idval', 20)->primary(); // Cambiado a idval
            $table->string('idser', 10); // Cambiado a idser
            $table->unsignedBigInteger('idpro'); // Cambiado a idpro
            $table->decimal('costoval', 5, 2)->nullable(); // Cambiado a costoval
            $table->integer('pantminval')->nullable(); // Cambiado a pantminval
            $table->integer('pantmaxval')->nullable(); // Cambiado a pantmaxval
            $table->integer('mesesval')->nullable(); // Cambiado a mesesval
            $table->timestamps(); // Optional if needed

            // Foreign key constraints
            $table->foreign('idser')
                ->references('idser')
                ->on('servicios')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->foreign('idpro')
                ->references('idpro')
                ->on('proveedores')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('servicios');
        Schema::dropIfExists('valores');
    }
};
