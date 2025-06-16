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
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared("
                -- VISTA USUARIOS ACTIVOS 
                CREATE OR REPLACE VIEW view_usuarios_activos AS
                SELECT 
                    v.IDCLI,
                    cl.NOMBRECLI AS nombre_cliente,
                    dv.idven,
                    dv.iddet,
                    p.IDCUE,  -- Relacionamos el perfil con la cuenta a través de IDCUE
                    p.NUMEROPER AS perfil,  -- Número de perfil desde la tabla PERFILES
                    dv.FECHAVENdet AS fecha_vencimiento
                FROM 
                    DETALLES_VENTA dv
                    INNER JOIN VENTAS v ON dv.IDVEN = v.IDVEN  -- Conectamos DETALLES_VENTA con VENTAS
                    INNER JOIN CLIENTES cl ON v.IDCLI = cl.IDCLI  -- Conectamos VENTAS con CLIENTES
                    INNER JOIN PERFILES p ON dv.IDPER = p.IDPER  -- Conectamos DETALLES_VENTA con PERFILES
                    INNER JOIN CUENTAS c ON p.IDCUE = c.IDCUE  -- Conectamos PERFILES con CUENTAS
                WHERE 
                    dv.ACTIVODET = TRUE;  -- Filtra solo los detalles de venta activos

                --CALCULAR EL TOTAL PAGADO DE UN CLIENTE EN UN MES ESPECIFICO
                CREATE OR REPLACE FUNCTION calcular_total_pagado_mes(
                    cliente_id INTEGER,
                    mes INTEGER,
                    anio INTEGER
                ) RETURNS DECIMAL(10, 2) AS $$
                DECLARE
                    total_pagado DECIMAL(10, 2);
                BEGIN
                    -- Calcular el total pagado por el cliente en el mes y año especificados
                    SELECT COALESCE(SUM(TOTALPAGOVEN), 0) INTO total_pagado
                    FROM VENTAS
                    WHERE IDCLI = cliente_id
                    AND EXTRACT(MONTH FROM FECHAVEN) = mes
                    AND EXTRACT(YEAR FROM FECHAVEN) = anio;
                    RETURN total_pagado;
                END;
                $$ LANGUAGE plpgsql;

                -- VISTA CLIENTES USUARIOS
                CREATE OR REPLACE VIEW view_clientes_usuarios AS
                SELECT 
                    u.IDCLI,
                    u.nombre_cliente,
                    COUNT(u.IDCLI) AS usuarios,
                    calcular_total_pagado_mes(
                        CAST(u.IDCLI AS INTEGER), 
                        CAST(EXTRACT(MONTH FROM CURRENT_DATE) AS INTEGER), 
                        CAST(EXTRACT(YEAR FROM CURRENT_DATE) AS INTEGER)
                    ) AS facturado
                    FROM view_usuarios_activos u
                    GROUP BY u.IDCLI, u.nombre_cliente;
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
        DB::unprepared("
        DROP FUNCTION IF EXISTS calcular_total_pagado_mes;
        DROP VIEW IF EXISTS view_usuarios_activos;
        DROP VIEW IF EXISTS view_clientes_usuarios;
        ");
        }
    }
};
