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
        // Agregar columna tipo_cuenta si no existe
        if (!Schema::hasColumn('cuentas', 'tipo_cuenta')) {
            Schema::table('cuentas', function (Blueprint $table) {
                $table->enum('tipo_cuenta', ['completa', 'individual'])
                      ->default('completa')
                      ->after('activocue')
                      ->comment('Tipo de cuenta: completa (SERVICIO-N) o individual (IND.SERVICIO-N)');
            });
        }

        // Actualizar cuentas existentes que empiezan con IND
        DB::statement("
            UPDATE cuentas
            SET tipo_cuenta = 'individual'
            WHERE idcue LIKE 'IND%'
        ");

        // Crear el trigger para generar idcue automáticamente
        DB::unprepared('DROP TRIGGER IF EXISTS trg_generar_idcue');

        DB::unprepared("
            CREATE TRIGGER trg_generar_idcue
            BEFORE INSERT ON cuentas
            FOR EACH ROW
            BEGIN
                DECLARE servicio_prefix VARCHAR(20);
                DECLARE tipo_prefix VARCHAR(10);
                DECLARE siguiente_numero INT;
                DECLARE nuevo_idcue VARCHAR(50);

                -- Solo generar si idcue está vacío o NULL
                IF NEW.idcue IS NULL OR NEW.idcue = '' THEN
                    -- Obtener el servicio desde el valor
                    SELECT idser INTO servicio_prefix
                    FROM valores
                    WHERE idval = NEW.idval;

                    -- Determinar el prefijo según el tipo de cuenta
                    IF NEW.tipo_cuenta = 'individual' THEN
                        SET tipo_prefix = 'IND.';
                    ELSE
                        SET tipo_prefix = '';
                    END IF;

                    -- Buscar el siguiente número disponible
                    SELECT COALESCE(MAX(
                        CAST(
                            SUBSTRING_INDEX(
                                REPLACE(idcue COLLATE utf8mb4_unicode_ci,
                                        CONCAT(tipo_prefix, servicio_prefix, '-') COLLATE utf8mb4_unicode_ci,
                                        '' COLLATE utf8mb4_unicode_ci),
                                '.',
                                1
                            ) AS UNSIGNED
                        )
                    ), 0) + 1
                    INTO siguiente_numero
                    FROM cuentas
                    WHERE idcue LIKE CONCAT(tipo_prefix, servicio_prefix, '-%')
                      AND idcue NOT LIKE '%Atencion';

                    -- Generar el nuevo idcue
                    SET nuevo_idcue = CONCAT(tipo_prefix, servicio_prefix, '-', siguiente_numero);

                    -- Asignar el nuevo idcue
                    SET NEW.idcue = nuevo_idcue;
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar el trigger
        DB::unprepared('DROP TRIGGER IF EXISTS trg_generar_idcue');

        // Eliminar la columna tipo_cuenta
        if (Schema::hasColumn('cuentas', 'tipo_cuenta')) {
            Schema::table('cuentas', function (Blueprint $table) {
                $table->dropColumn('tipo_cuenta');
            });
        }
    }
};
