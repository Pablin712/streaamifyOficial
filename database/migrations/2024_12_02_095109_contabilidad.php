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
        Schema::create('contabilidad', function (Blueprint $table) {
            $table->id('idcon');  // idcon como clave primaria, autoincremental
            $table->integer('mes')->default(DB::raw('EXTRACT(MONTH FROM CURRENT_DATE)'));  // mes por defecto es el mes actual
            $table->integer('año')->default(DB::raw('EXTRACT(YEAR FROM CURRENT_DATE)'));  // año por defecto es el año actual
            $table->string('detalle',20);  // detalle de la transacción
            $table->integer('num_cuentas');  // número de cuentas involucradas
            $table->integer('num_usuarios');  // número de usuarios involucrados
            $table->decimal('ingresos', 15, 2);  // ingresos (con 2 decimales)
            $table->decimal('costos', 15, 2);  // costos (con 2 decimales)
            $table->decimal('ganancias', 15, 2);  // ganancias (con 2 decimales)
            $table->decimal('renta', 5, 2);  // renta (con 2 decimales)
            
            // Para agregar índices o relaciones, si es necesario
            // $table->timestamps();  // Si deseas incluir las marcas de tiempo created_at y updated_at
        });

        DB::unprepared('
            CREATE OR REPLACE FUNCTION calcular_ganancias() 
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.ganancias := NEW.ingresos - NEW.costos;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER before_insert_contabilidad
            BEFORE INSERT ON contabilidad
            FOR EACH ROW
            EXECUTE FUNCTION calcular_ganancias();
        ');
        DB::unprepared('
            CREATE OR REPLACE FUNCTION calcular_renta() 
            RETURNS TRIGGER AS $$
            BEGIN
                -- Asegurarse de que los ingresos no sean 0 para evitar división por 0
                IF NEW.ingresos != 0 THEN
                    NEW.renta := (NEW.ganancias / NEW.ingresos) * 100;
                ELSE
                    NEW.renta := 0; -- Si los ingresos son 0, asignamos 0 a la renta
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
            CREATE TRIGGER before_insert_update_contabilidad
            BEFORE INSERT OR UPDATE ON contabilidad
            FOR EACH ROW
            EXECUTE FUNCTION calcular_renta();
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS before_insert_contabilidad ON contabilidad;');
        DB::unprepared('DROP FUNCTION IF EXISTS calcular_ganancias;');
        DB::unprepared('DROP TRIGGER IF EXISTS before_insert_update_contabilidad ON contabilidad');
        DB::unprepared('DROP FUNCTION IF EXISTS calcular_renta');
        Schema::dropIfExists('contabilidad');
    }
};
