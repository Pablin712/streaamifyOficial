<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metas', function (Blueprint $table) {
            $table->id('idmet');

            // Codigo del KPI dentro del catalogo de MetaService.
            $table->string('kpi', 60);

            $table->decimal('objetivo', 12, 2);

            // mensual | anual. El periodo decide como se reparte el ritmo.
            $table->string('periodo', 12)->default('mensual');

            // NULL = la meta se repite en cada periodo (meta permanente).
            // Con valor = solo aplica a ese mes/anio concreto.
            $table->unsignedSmallInteger('anio')->nullable();
            $table->unsignedTinyInteger('mes')->nullable();

            // Por debajo de este % de la proyeccion la tarjeta pasa a rojo.
            $table->unsignedTinyInteger('umbral_atencion')->default(90);

            $table->boolean('activo')->default(true);
            $table->string('nota', 255)->nullable();

            $table->timestamps();

            $table->index(['activo', 'periodo']);
            $table->unique(['kpi', 'anio', 'mes'], 'metas_kpi_periodo_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas');
    }
};
