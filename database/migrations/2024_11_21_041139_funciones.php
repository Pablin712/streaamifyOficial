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
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('
        -- DROP PARA SEQUENCE ventas_diarias_seq
        DROP SEQUENCE ventas_diarias_seq;

        -- DROP PARA FUNCION obtener_costo_mes_actual
        DROP FUNCTION IF EXISTS obtener_costo_mes_actual(VARCHAR);

        -- DROP PARA FUNCION contar_usuarios_perfil
        DROP FUNCTION IF EXISTS contar_usuarios_perfil(VARCHAR, INTEGER);

        -- DROP PARA FUNCION contar_usuarios_activos
        DROP FUNCTION IF EXISTS contar_usuarios_activos(VARCHAR);

        -- DROP PARA FUNCION calcular_total_pagado_mes
        DROP FUNCTION IF EXISTS calcular_total_pagado_mes(BIGINT, INTEGER, INTEGER);

        -- DROP PARA FUNCION NUM_CUENTAS_SERVICIO
        DROP FUNCTION IF EXISTS NUM_CUENTAS_SERVICIO(VARCHAR);

        -- DROP PARA FUNCION SUMA_INGRESOS_DE_SERVICIO_MES
        DROP FUNCTION IF EXISTS SUMA_INGRESOS_DE_SERVICIO_MES(VARCHAR, INT);
    ');
    }
};
