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
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL
            // Crear la función
            DB::statement("
                CREATE OR REPLACE FUNCTION generar_idval()
                RETURNS trigger AS $$
                DECLARE
                    tipo_valor VARCHAR;
                    proveedor_nombre VARCHAR;
                BEGIN
                    tipo_valor := SUBSTRING(NEW.tipoval FROM 1 FOR 3);
                    SELECT SPLIT_PART(nombrepro, ' ', 1) INTO proveedor_nombre
                    FROM proveedores WHERE idpro = NEW.idpro;
                    NEW.idval := NEW.idser || '-' || proveedor_nombre || '-' || tipo_valor || '-' || NEW.mesesval || 'm';
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
            ");

            // Eliminar trigger si ya existe
            DB::statement("DROP TRIGGER IF EXISTS trigger_generar_idval ON valores;");

            // Crear el trigger
            DB::statement("
                CREATE TRIGGER trigger_generar_idval
                BEFORE INSERT ON valores
                FOR EACH ROW
                EXECUTE FUNCTION generar_idval();
            ");
        } elseif ($driver === 'mysql') {
            // MySQL
            // Eliminar el trigger si existe
            DB::statement("DROP TRIGGER IF EXISTS trigger_generar_idval");

            // Crear el trigger (sin DELIMITER)
            DB::statement("
                CREATE TRIGGER trigger_generar_idval
                BEFORE INSERT ON valores
                FOR EACH ROW
                BEGIN
                    DECLARE tipo_valor VARCHAR(10);
                    DECLARE proveedor_nombre VARCHAR(100);

                    SET tipo_valor = LEFT(NEW.tipoval, 3);

                    SELECT SUBSTRING_INDEX(nombrepro, ' ', 1) INTO proveedor_nombre
                    FROM proveedores WHERE idpro = NEW.idpro LIMIT 1;

                    SET NEW.idval = CONCAT(NEW.idser, '-', proveedor_nombre, '-', tipo_valor, '-', NEW.mesesval, 'm');
                END
            ");
        } else {
            throw new \Exception("Motor de base de datos no soportado: $driver");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("DROP TRIGGER IF EXISTS trigger_generar_idval ON valores;");
            DB::statement("DROP FUNCTION IF EXISTS generar_idval();");
        } elseif ($driver === 'mysql') {
            DB::statement("DROP TRIGGER IF EXISTS trigger_generar_idval;");
        }
    }
};
