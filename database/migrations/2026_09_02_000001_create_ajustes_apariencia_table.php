<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Apariencia global de la plataforma.
 *
 * Hasta ahora el tema y el modo oscuro vivian solo en localStorage, asi que
 * cada navegador y cada sesion de empleado tenia el suyo: lo que elegia el
 * administrador no llegaba a nadie mas. Esta tabla es la fuente de verdad
 * unica y global; el navegador ya solo la refleja.
 *
 * Es una tabla de una sola fila (singleton). AparienciaService se encarga de
 * crearla y cachearla.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ajustes_apariencia', function (Blueprint $table) {
            $table->id();

            // Tema base elegido por el administrador.
            $table->string('tema', 40)->default('default');

            // Modo oscuro global. Se aplica encima de cualquier tema.
            $table->boolean('modo_oscuro')->default(false);

            // Si esta activo, un tema de temporada cuya ventana de fechas este
            // corriendo tiene prioridad sobre el tema base.
            $table->boolean('auto_temporada')->default(true);

            // Quien hizo el ultimo cambio, para poder auditarlo desde la vista.
            $table->string('actualizado_por', 120)->nullable();

            $table->timestamps();
        });

        // Fila unica inicial.
        DB::table('ajustes_apariencia')->insert([
            'tema'           => 'default',
            'modo_oscuro'    => false,
            'auto_temporada' => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ajustes_apariencia');
    }
};
