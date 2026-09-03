<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Separa lo GLOBAL de lo PERSONAL.
 *
 * El TEMA (Navidad, Neón, Mundial…) lo fija el administrador y lo ve todo el
 * mundo: eso sigue en ajustes_apariencia.
 *
 * El MODO CLARO / OSCURO no: es preferencia de cada persona, como en cualquier
 * aplicación. Pasa a guardarse por empleado, en el servidor, para que le siga
 * a través de sus dispositivos. El valor por defecto es 'system', es decir,
 * seguir la preferencia del sistema operativo.
 *
 * Por eso se elimina ajustes_apariencia.modo_oscuro: si el administrador
 * elegía oscuro, se lo imponía a todos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            // 'system' = seguir al sistema operativo | 'light' | 'dark'
            $table->string('preferencia_tema', 10)->default('system')->after('nombreemp');
        });

        Schema::table('ajustes_apariencia', function (Blueprint $table) {
            $table->dropColumn('modo_oscuro');
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn('preferencia_tema');
        });

        Schema::table('ajustes_apariencia', function (Blueprint $table) {
            $table->boolean('modo_oscuro')->default(false);
        });
    }
};
