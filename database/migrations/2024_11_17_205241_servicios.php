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
            $table->string('idservicio', 10)->primary();
            $table->string('nombre', 20);
            $table->decimal('completo', 5, 2)->nullable();
            $table->decimal('precio', 5, 2)->nullable();
            $table->decimal('combo', 5, 2)->nullable();
            $table->decimal('reventa', 5, 2)->nullable();
            $table->decimal('revcomp', 5, 2)->nullable();
            $table->timestamps(); // Optional if needed
        });
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id('idproveedor'); // Auto-increment primary key
            $table->string('nombre', 20);
            $table->string('telefono', 15)->nullable();
            $table->timestamps(); // Optional if needed
        });
        Schema::create('valores', function (Blueprint $table) {
            $table->string('idvalor', 20)->primary();
            $table->string('idservicio', 10);
            $table->unsignedBigInteger('idproveedor');
            $table->decimal('costo', 5, 2)->nullable();
            $table->integer('pantmin')->nullable();
            $table->integer('pantmax')->nullable();
            $table->integer('meses')->nullable();
            $table->timestamps(); // Optional if needed

            // Foreign key constraints
            $table->foreign('idservicio')
                ->references('idservicio')
                ->on('servicios')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->foreign('idproveedor')
                ->references('idproveedor')
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
        Schema::dropIfExists('servicios');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('valores');
    }
};
