<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empleado_id');
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->text('notas')->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->boolean('cancelado')->default(false);
            $table->timestamps();

            $table->foreign('empleado_id')->references('idemp')->on('empleados')->onDelete('cascade');
            $table->foreign('creado_por')->references('idemp')->on('empleados')->onDelete('set null');
            $table->index(['empleado_id', 'fecha']);
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
