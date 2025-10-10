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
        // Trigger para generar código referidor en clientes
        DB::unprepared('
            DROP TRIGGER IF EXISTS trg_insert_codigo_referidor;
            CREATE TRIGGER trg_insert_codigo_referidor 
            BEFORE INSERT ON clientes FOR EACH ROW 
            BEGIN
                SET NEW.codigo_referidor = UPPER(CONCAT(SUBSTRING_INDEX(NEW.nombrecli, " ", 1), "-", LPAD(NEW.idcli, 3, "0")));
            END
        ');

        DB::unprepared('
            DROP TRIGGER IF EXISTS trg_update_codigo_referidor;
            CREATE TRIGGER trg_update_codigo_referidor 
            BEFORE UPDATE ON clientes FOR EACH ROW 
            BEGIN
                IF NEW.nombrecli != OLD.nombrecli THEN
                    SET NEW.codigo_referidor = UPPER(CONCAT(SUBSTRING_INDEX(NEW.nombrecli, " ", 1), "-", LPAD(NEW.idcli, 3, "0")));
                END IF;
            END
        ');

        // Trigger para generar ID de venta
        DB::unprepared('
            DROP TRIGGER IF EXISTS trg_generar_idventa;
            CREATE TRIGGER trg_generar_idventa 
            BEFORE INSERT ON ventas FOR EACH ROW 
            BEGIN
                DECLARE secuencia BIGINT;
                DECLARE establecimiento VARCHAR(3) DEFAULT "001";
                DECLARE facturero VARCHAR(3) DEFAULT "001";

                INSERT INTO secuencia_factura () VALUES ();
                SET secuencia = LAST_INSERT_ID();

                SET NEW.idven = CONCAT(establecimiento, "-", facturero, "-", LPAD(secuencia, 9, "0"));
            END
        ');

        // Triggers para actualizar total de venta
        DB::unprepared('
            DROP TRIGGER IF EXISTS trg_actualizar_total_venta_insert;
            CREATE TRIGGER trg_actualizar_total_venta_insert 
            AFTER INSERT ON detalles_venta FOR EACH ROW 
            BEGIN
                UPDATE ventas
                SET totalpagoven = (
                    SELECT COALESCE(SUM(montodet), 0)
                    FROM detalles_venta
                    WHERE idven = NEW.idven
                )
                WHERE idven = NEW.idven;
            END
        ');

        DB::unprepared('
            DROP TRIGGER IF EXISTS trg_actualizar_total_venta_update;
            CREATE TRIGGER trg_actualizar_total_venta_update 
            AFTER UPDATE ON detalles_venta FOR EACH ROW 
            BEGIN
                UPDATE ventas
                SET totalpagoven = (
                    SELECT COALESCE(SUM(montodet), 0)
                    FROM detalles_venta
                    WHERE idven = NEW.idven
                )
                WHERE idven = NEW.idven;
            END
        ');

        DB::unprepared('
            DROP TRIGGER IF EXISTS trg_actualizar_total_venta_delete;
            CREATE TRIGGER trg_actualizar_total_venta_delete 
            AFTER DELETE ON detalles_venta FOR EACH ROW 
            BEGIN
                UPDATE ventas
                SET totalpagoven = (
                    SELECT COALESCE(SUM(montodet), 0)
                    FROM detalles_venta
                    WHERE idven = OLD.idven
                )
                WHERE idven = OLD.idven;
            END
        ');

        // Trigger para generar idval en valores
        DB::unprepared('
            DROP TRIGGER IF EXISTS trigger_generar_idval;
            CREATE TRIGGER trigger_generar_idval 
            BEFORE INSERT ON valores FOR EACH ROW 
            BEGIN
                DECLARE tipo_valor VARCHAR(10);
                DECLARE proveedor_nombre VARCHAR(100);

                SET tipo_valor = LEFT(NEW.tipoval, 3);

                SELECT SUBSTRING_INDEX(nombrepro, " ", 1) INTO proveedor_nombre
                FROM proveedores 
                WHERE idpro = NEW.idpro 
                LIMIT 1;

                SET NEW.idval = CONCAT(NEW.idser, "-", proveedor_nombre, "-", tipo_valor, "-", NEW.mesesval, "m");
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_insert_codigo_referidor');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_update_codigo_referidor');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_generar_idventa');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_actualizar_total_venta_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_actualizar_total_venta_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_actualizar_total_venta_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_generar_idval');
    }
};