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
            CREATE OR REPLACE FUNCTION obtener_costo_mes_actual(id_cuenta_param VARCHAR)
            RETURNS DECIMAL(8,2) AS $$
            BEGIN
                RETURN COALESCE(
                    (SELECT SUM(MONTO)
                    FROM COSTOS
                    WHERE IDCUENTA = id_cuenta_param
                    AND EXTRACT(MONTH FROM FECHACOSTO) = EXTRACT(MONTH FROM CURRENT_DATE)
                    AND EXTRACT(YEAR FROM FECHACOSTO) = EXTRACT(YEAR FROM CURRENT_DATE)), 0);
            END;
            $$ LANGUAGE plpgsql;


            --CONTAR USUARIOS ACTIVOS EN UN PERFIL DE UNA CUENTA
            CREATE OR REPLACE FUNCTION contar_usuarios_perfil(id_cuenta_param VARCHAR, perfil_numero INTEGER)
            RETURNS INTEGER AS $$
            BEGIN
                RETURN COALESCE(
                    (SELECT COUNT(*)
                    FROM DETALLES_VENTA
                    WHERE IDCUENTA = id_cuenta_param
                    AND PERFIL = perfil_numero
                    AND ACTIVO=TRUE), 0);
            END;
            $$ LANGUAGE plpgsql;

            --CONTAR USUARIOS ACTIVOS TOTALES EN LA CUENTA
            CREATE OR REPLACE FUNCTION contar_usuarios_activos(id_cuenta_param VARCHAR)
            RETURNS INTEGER AS $$
            BEGIN
                RETURN COALESCE(
                    (SELECT COUNT(*)
                    FROM DETALLES_VENTA
                    WHERE IDCUENTA = id_cuenta_param
                    AND ACTIVO = TRUE), 0);
            END;
            $$ LANGUAGE plpgsql;


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
                SELECT COALESCE(SUM(TOTALPAGO), 0) INTO total_pagado
                FROM VENTAS
                WHERE IDCLIENTE = cliente_id
                AND EXTRACT(MONTH FROM FECHAVENTA) = mes
                AND EXTRACT(YEAR FROM FECHAVENTA) = anio;

                RETURN total_pagado;
            END;
            $$ LANGUAGE plpgsql;




            --FUNCIONES ESPECIALES, ESPECIFICAS PARA LAS ESTADISTICAS
            --A CONTINUACIÓN
            --FUNCIONES DE ESTADÍSTICA
            CREATE OR REPLACE FUNCTION NUM_CUENTAS_SERVICIO(IDSERVICIO VARCHAR(10))
            RETURNS INT AS $$
            DECLARE TOTAL INT;
            BEGIN
                IF IDSERVICIO='OTRO' THEN
                    SELECT COUNT(CU.IDCUENTA) INTO TOTAL FROM CUENTAS CU
                    JOIN VALORES VA ON CON.IDVALOR=CU.IDVALOR
                    WHERE VA.IDSERVICIO
                    NOT IN (
                            'NETFLIX', 'MAX', 'PRIME', 'DISNEYP','DISNEYS', 'CRUNCHY', 'SPOTIFY', 'MAGIS', 'PARAMOUNT'
                        );
                ELSE
                    SELECT COUNT(CU.IDCUENTA) INTO TOTAL FROM CUENTAS CU
                    JOIN VALORES VA ON VA.IDVALOR=CU.IDVALOR
                    WHERE VA.IDSERVICIO=NUM_CUENTAS_SERVICIO.IDSERVICIO;
                END IF;
                RETURN TOTAL;
            END;
            $$ LANGUAGE PLPGSQL;

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

            -- DROP PARA FUNCION obtener_costo_mes_actual
            DROP FUNCTION IF EXISTS obtener_costo_mes_actual(VARCHAR);

            -- DROP PARA FUNCION contar_usuarios_perfil
            DROP FUNCTION IF EXISTS contar_usuarios_perfil(VARCHAR, INTEGER);

            -- DROP PARA FUNCION contar_usuarios_activos
            DROP FUNCTION IF EXISTS contar_usuarios_activos(VARCHAR);

            -- DROP PARA FUNCION calcular_total_pagado_mes
            DROP FUNCTION IF EXISTS calcular_total_pagado_mes(INTEGER, INTEGER, INTEGER);

            -- DROP PARA FUNCION NUM_CUENTAS_SERVICIO
            DROP FUNCTION IF EXISTS NUM_CUENTAS_SERVICIO(VARCHAR);

            -- DROP PARA FUNCION SUMA_INGRESOS_DE_SERVICIO_MES
            DROP FUNCTION IF EXISTS SUMA_INGRESOS_DE_SERVICIO_MES(VARCHAR, INT);
        ');
    }
};
