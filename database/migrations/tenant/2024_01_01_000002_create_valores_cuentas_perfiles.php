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
        Schema::create('valores', function (Blueprint $table) {
            $table->string('idval', 30)->primary();
            $table->string('idser', 10);
            $table->unsignedBigInteger('idpro');
            $table->decimal('costoval', 5, 2)->nullable();
            $table->enum('tipoval', ['completo', 'individual', 'hibrido'])->default('completo')
                  ->comment('Tipo de valor: completo, individual o híbrido');
            $table->integer('pantminval')->nullable();
            $table->integer('pantmaxval')->nullable();
            $table->integer('mesesval')->nullable();
            $table->timestamps();
            $table->boolean('activoval')->default(1);
            $table->text('bot')->nullable();
            
            $table->foreign('idser')->references('idser')->on('servicios');
            $table->foreign('idpro')->references('idpro')->on('proveedores');
        });

        Schema::create('cuentas', function (Blueprint $table) {
            $table->string('idcue', 50)->primary();
            $table->string('idval', 30)->nullable();
            $table->date('fechavencue');
            $table->string('usuariocue', 50);
            $table->string('contrasenacue', 50);
            $table->boolean('caidacue');
            $table->boolean('activocue')->default(1);
            $table->timestamps();
            
            $table->foreign('idval')->references('idval')->on('valores')
                  ->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('perfiles', function (Blueprint $table) {
            $table->string('idper', 50)->primary();
            $table->string('idcue', 50)->nullable();
            $table->integer('numeroper');
            $table->string('pinper', 255)->default('ninguno');
            
            $table->foreign('idcue')->references('idcue')->on('cuentas')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfiles');
        Schema::dropIfExists('cuentas');
        Schema::dropIfExists('valores');
    }
};