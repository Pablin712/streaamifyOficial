<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un registro por cada vez que una cuenta se marca como dañada
     * (caidacue false->true) hasta que se repara (true->false). Se llena
     * automaticamente via CuentaObserver, sin importar desde donde se
     * togglee caidacue. No existe historico antes de esta tabla: las
     * cuentas ya dañadas al desplegar esto arrancan con inicio=ahora
     * (fecha de inicio real desconocida, ver docs/finanzas).
     */
    public function up(): void
    {
        Schema::create('cuenta_incidencias', function (Blueprint $table) {
            $table->id();
            $table->string('idcue', 50);
            $table->string('servicio_idser', 20)->nullable();
            $table->timestamp('inicio');
            $table->timestamp('fin')->nullable();
            $table->unsignedInteger('duracion_minutos')->nullable();
            $table->timestamps();

            $table->index(['idcue', 'fin'], 'ci_idcue_fin_idx');
            $table->index('servicio_idser', 'ci_servicio_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuenta_incidencias');
    }
};
