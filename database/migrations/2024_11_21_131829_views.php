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
        DB::unprepared("
            --VISTA CUENTAS PERFILES GENERAL
            CREATE OR REPLACE VIEW view_cuentas_perfiles AS
            SELECT 
                c.IDCUE,
                c.USUARIOCUE,
                obtener_costo_mes_actual(c.IDCUE) AS costo_mes_actual,
                c.FECHAVENCUE AS fecha_vencimiento,
                -- Llamadas a la función para contar usuarios por perfil
                contar_usuarios_perfil(c.IDCUE, 1) AS P1,
                contar_usuarios_perfil(c.IDCUE, 2) AS P2,
                contar_usuarios_perfil(c.IDCUE, 3) AS P3,
                contar_usuarios_perfil(c.IDCUE, 4) AS P4,
                contar_usuarios_perfil(c.IDCUE, 5) AS P5,
                contar_usuarios_perfil(c.IDCUE, 6) AS P6,
                contar_usuarios_perfil(c.IDCUE, 7) AS P7,
                -- Total de usuarios activos
                contar_usuarios_activos(c.IDCUE) AS total_usuarios_activos
            FROM 
                CUENTAS c;

            --VISTA USUARIOS ACTIVOS
            CREATE OR REPLACE VIEW view_usuarios_activos AS
            SELECT 
                v.IDCLI,
                cl.NOMBRECLI AS nombre_cliente,
                dv.IDCUE,
                dv.PERDET AS perfil,
                c.FECHAVENCUE AS fecha_vencimiento_cuenta
            FROM 
                DETALLES_VENTA dv
                INNER JOIN VENTAS v ON dv.IDVEN = v.IDVEN
                INNER JOIN CLIENTES cl ON v.IDCLI = cl.IDCLI
                INNER JOIN CUENTAS c ON dv.IDCUE = c.IDCUE
            WHERE 
                dv.ACTIVODET = TRUE;


            CREATE OR REPLACE VIEW view_clientes_usuarios AS
            SELECT 
                u.IDCLI,
                u.nombre_cliente,
                COUNT(u.IDCLI) AS usuarios,
                calcular_total_pagado_mes(
                    u.IDCLI, 
                    CAST(EXTRACT(MONTH FROM CURRENT_DATE) AS INTEGER), 
                    CAST(EXTRACT(YEAR FROM CURRENT_DATE) AS INTEGER)
                ) AS facturado
            FROM view_usuarios_activos u
            GROUP BY u.IDCLI, u.nombre_cliente;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("
            DROP VIEW IF EXISTS view_cuentas_perfiles;
            DROP VIEW IF EXISTS view_usuarios_activos;
            DROP VIEW IF EXISTS view_CLIENTES_USUARIOS;
        ");
    }
};
