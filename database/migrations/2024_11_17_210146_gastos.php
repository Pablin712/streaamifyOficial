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
        Schema::create('tipo_gasto', function (Blueprint $table) {
            $table->id('idtip'); // Cambiado a idtip
            $table->string('detalletip', 30); // Cambiado a detalletip
            $table->timestamps(); // Optional: created_at and updated_at
        });

        Schema::create('gastos', function (Blueprint $table) {
            $table->id('idgas'); // Cambiado a idgas
            $table->unsignedBigInteger('idtip'); // Cambiado a idtip
            $table->date('fechagas')->default(DB::raw('CURRENT_DATE')); // Cambiado a fechagas
            $table->decimal('montogas', 8, 2); // Cambiado a montogas
            $table->string('descripciongas', 50)->nullable(); // Cambiado a descripciongas
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('idtip')
                ->references('idtip')
                ->on('tipo_gasto')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos');
        Schema::dropIfExists('tipo_gasto');
    }
};
