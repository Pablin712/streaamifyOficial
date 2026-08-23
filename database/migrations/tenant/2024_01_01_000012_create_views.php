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
        // Vista de usuarios activos
        DB::statement('
            CREATE OR REPLACE VIEW view_usuarios_activos AS
            SELECT 
                v.idcli,
                cl.nombrecli AS nombre_cliente,
                dv.idven,
                dv.iddet,
                p.idcue,
                p.numeroper AS perfil,
                dv.fechavendet AS fecha_vencimiento
            FROM detalles_venta dv
            JOIN ventas v ON dv.idven = v.idven
            JOIN clientes cl ON v.idcli = cl.idcli
            JOIN perfiles p ON dv.idper = p.idper
            JOIN cuentas c ON p.idcue = c.idcue
            WHERE dv.activodet = 1
        ');

        // Vista de clientes usuarios (requiere función calcullar_total_pagado_mes)
        DB::statement('
            CREATE OR REPLACE VIEW view_clientes_usuarios AS
            SELECT 
                u.idcli,
                u.nombre_cliente,
                COUNT(u.idcli) AS usuarios,
                0.00 AS facturado
            FROM view_usuarios_activos u
            GROUP BY u.idcli, u.nombre_cliente
        ');

        // Vista de ventas mensuales
        DB::statement('
            CREATE OR REPLACE VIEW ventas_mensuales AS
            SELECT 
                YEAR(fechaven) AS anio,
                MONTH(fechaven) AS mes,
                COUNT(*) AS total_ventas,
                SUM(totalpagoven) AS total_monto
            FROM ventas
            GROUP BY YEAR(fechaven), MONTH(fechaven)
            ORDER BY YEAR(fechaven) DESC, MONTH(fechaven) DESC
        ');

        // Vista de usuarios activos mensuales
        DB::statement('
            CREATE OR REPLACE VIEW usuarios_activos_mensuales AS
            SELECT 
                YEAR(date) AS anio,
                MONTH(date) AS mes,
                ROUND(AVG(active_users), 0) AS promedio_usuarios_activos
            FROM daily_statistics
            GROUP BY YEAR(date), MONTH(date)
            ORDER BY YEAR(date) DESC, MONTH(date) DESC
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS usuarios_activos_mensuales');
        DB::statement('DROP VIEW IF EXISTS ventas_mensuales');
        DB::statement('DROP VIEW IF EXISTS view_clientes_usuarios');
        DB::statement('DROP VIEW IF EXISTS view_usuarios_activos');
    }
};