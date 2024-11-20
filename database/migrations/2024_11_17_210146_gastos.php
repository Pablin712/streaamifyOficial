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
            $table->id('idtipo'); // Auto-increment primary key
            $table->string('detalle', 30);
            $table->timestamps(); // Optional: created_at and updated_at
        });
        Schema::create('gastos', function (Blueprint $table) {
            $table->id('idgasto'); // Auto-increment primary key
            $table->unsignedBigInteger('idtipo'); // Foreign key to tipo_gasto
            $table->date('fechagasto')->default(DB::raw('CURRENT_DATE'));
            $table->decimal('monto', 8, 2);
            $table->string('descripcion', 50)->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('idtipo')
                ->references('idtipo')
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
        Schema::dropIfExists('tipo_gasto');
        Schema::dropIfExists('gastos');
    }
};
