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
        Schema::create('costos', function (Blueprint $table) {
            $table->id('idcos');
            $table->string('idcue', 50)->nullable();
            $table->date('fechacos');
            $table->decimal('montocos', 8, 2);
            $table->string('descripcioncos', 50)->nullable();

            $table->foreign('idcue')->references('idcue')->on('cuentas')
                  ->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id('idman');
            $table->string('idcue', 20)->unique();
            $table->string('descripcionman', 255);
            $table->date('fechaman');

            $table->foreign('idcue')->references('idcue')->on('cuentas')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costos');
        Schema::dropIfExists('mantenimientos');
    }
};
