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
            --DEFINIR IDVENTA
            --NOTA: SE UNE ANTES DEL TRIGGER GENERARIDVENTA
            CREATE SEQUENCE ventas_diarias_seq
            START 1
            MINVALUE 1
            MAXVALUE 999
            CYCLE;

            --OBTIENE EL COSTO QUE SE HA ASUMIDO EN EL MES DE UNA CUENTA ESPECIFICA DEL NEGOCIO
            CREATE OR REPLACE FUNCTION obtener_costo_mes_actual(idcue_param VARCHAR)
            RETURNS DECIMAL(8,2) AS $$
            BEGIN
                RETURN COALESCE(
                    (SELECT SUM(MONTOCOS)
                    FROM COSTOS
                    WHERE IDCUE = idcue_param
                    AND EXTRACT(MONTH FROM FECHACOS) = EXTRACT(MONTH FROM CURRENT_DATE)
                    AND EXTRACT(YEAR FROM FECHACOS) = EXTRACT(YEAR FROM CURRENT_DATE)), 0);
            END;
            $$ LANGUAGE plpgsql;



            --CONTAR USUARIOS ACTIVOS EN UN PERFIL DE UNA CUENTA
            CREATE OR REPLACE FUNCTION contar_usuarios_perfil(idcue_param VARCHAR, numper INTEGER)
            RETURNS INTEGER AS $$
            BEGIN
                RETURN COALESCE(
                    (SELECT COUNT(*)
                    FROM DETALLES_VENTA
                    WHERE IDCUE = idcue_param
                    AND PERDET = numper
                    AND ACTIVODET = TRUE), 0);
            END;
            $$ LANGUAGE plpgsql;

            --CONTAR USUARIOS ACTIVOS TOTALES EN LA CUENTA
            CREATE OR REPLACE FUNCTION contar_usuarios_activos(idcue_param VARCHAR)
            RETURNS INTEGER AS $$
            BEGIN
                RETURN COALESCE(
                    (SELECT COUNT(*)
                    FROM DETALLES_VENTA
                    WHERE IDCUE = idcue_param
                    AND ACTIVODET = TRUE), 0);
            END;
            $$ LANGUAGE plpgsql;

            --CALCULAR EL TOTAL PAGADO DE UN CLIENTE EN UN MES ESPECIFICO
            CREATE OR REPLACE FUNCTION calcular_total_pagado_mes(
                idcli BIGINT,
                mes INTEGER,
                anio INTEGER
            ) RETURNS DECIMAL(10, 2) AS $$
            DECLARE
                total_pagado DECIMAL(10, 2);
            BEGIN
                -- Calcular el total pagado por el cliente en el mes y año especificados
                SELECT COALESCE(SUM(TOTALPAGOVEN), 0) INTO total_pagado
                FROM VENTAS
                WHERE IDCLI = idcli
                AND EXTRACT(MONTH FROM FECHAVEN) = mes
                AND EXTRACT(YEAR FROM FECHAVEN) = anio;

                RETURN total_pagado;
            END;
            $$ LANGUAGE plpgsql;





            --FUNCIONES ESPECIALES, ESPECIFICAS PARA LAS ESTADISTICAS
            --A CONTINUACIÓN
            --FUNCIONES DE ESTADÍSTICA
            CREATE OR REPLACE FUNCTION num_cuentas_servicio(idser_param VARCHAR(10))
            RETURNS INTEGER AS $$
            DECLARE
                total INTEGER;
            BEGIN
                IF idser_param = 'OTRO' THEN
                    SELECT COUNT(CU.IDCUE) INTO total
                    FROM CUENTAS CU
                    JOIN VALORES VA ON VA.IDVAL = CU.IDVAL
                    WHERE VA.IDSER NOT IN (
                        'NETFLIX', 'MAX', 'PRIME', 'DISNEYP', 'DISNEYS', 
                        'CRUNCHY', 'SPOTIFY', 'MAGIS', 'PARAMOUNT'
                    );
                ELSE
                    SELECT COUNT(CU.IDCUE) INTO total
                    FROM CUENTAS CU
                    JOIN VALORES VA ON VA.IDVAL = CU.IDVAL
                    WHERE VA.IDSER = idser_param;
                END IF;

                RETURN total;
            END;
            $$ LANGUAGE plpgsql;


            CREATE OR REPLACE FUNCTION SUMA_INGRESOS_DE_SERVICIO_MES(IDSERVICIO VARCHAR(10), MES INT)
            RETURNS DECIMAL(8,2) AS $$
            DECLARE TOTAL DECIMAL(8,2);
            BEGIN
                IF IDSERVICIO = 'OTRO' THEN
                    SELECT COALESCE(SUM(DV.MONTO), 0) INTO TOTAL
                    FROM DETALLES_VENTA DV
                    JOIN VENTAS V ON V.IDVENTA = DV.IDVENTA
                    JOIN CUENTAS C ON C.IDCUENTA = DV.IDCUENTA
                    JOIN VALORES VA ON VA.IDVALOR = C.IDVALOR
                    WHERE EXTRACT(MONTH FROM V.FECHAVENTA) = MES
                    AND VA.IDSERVICIO NOT IN (
                        'NETFLIX', 'MAX', 'PRIME', 'DISNEYP','DISNEYS', 'CRUNCHY', 'SPOTIFY', 'MAGIS', 'PARAMOUNT'
                    );
                ELSE
                    SELECT COALESCE(SUM(DV.MONTO),0) INTO TOTAL
                    FROM DETALLES_VENTA DV
                    JOIN VENTAS V ON V.IDVENTA=DV.IDVENTA
                    JOIN CUENTAS C ON C.IDCUENTA = DV.IDCUENTA
                    JOIN VALORES VA ON VA.IDVALOR = C.IDVALOR
                    WHERE 
                        EXTRACT(MONTH FROM V.FECHAVENTA)=MES AND
                        SUMA_INGRESOS_DE_SERVICIO_MES.IDSERVICIO=VA.IDSERVICIO;
                END IF;
                RETURN TOTAL;
            END;
            $$ LANGUAGE PLPGSQL;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('
            -- DROP PARA SEQUENCE ventas_diarias_seq
            DROP SEQUENCE IF EXISTS ventas_diarias_seq;
            DROP SEQUENCE IF EXISTS ventas_diarias_seq CASCADE;

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
