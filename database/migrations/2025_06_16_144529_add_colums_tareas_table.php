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
        Schema::table('tareas', function (Blueprint $table) {
            $table->unsignedBigInteger('completada_por')->nullable()->after('fechalimit');
            $table->dateTime('fecha_completada')->nullable()->after('completada_por');
            // Si tienes la tabla empleados y quieres la relación:
            $table->foreign('completada_por')->references('idemp')->on('empleados')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            // Si agregaste la foreign key, primero elimínala:
            $table->dropForeign(['completada_por']);
            $table->dropColumn(['completada_por', 'fecha_completada']);
        });
    }
};
