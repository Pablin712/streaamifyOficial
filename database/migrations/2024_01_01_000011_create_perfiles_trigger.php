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
        // Trigger para insertar perfiles automáticamente al crear cuentas
        DB::unprepared('
            DROP TRIGGER IF EXISTS insertar_perfiles;
            CREATE TRIGGER insertar_perfiles 
            AFTER INSERT ON cuentas FOR EACH ROW 
            BEGIN
                -- Insertar perfiles para Netflix
                IF NEW.idcue LIKE "NETFLIX%" THEN
                    INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
                        (CONCAT(NEW.idcue, ".1"), NEW.idcue, 1, "1000"),
                        (CONCAT(NEW.idcue, ".2"), NEW.idcue, 2, "5555"),
                        (CONCAT(NEW.idcue, ".3"), NEW.idcue, 3, "8833"),
                        (CONCAT(NEW.idcue, ".4"), NEW.idcue, 4, "6622"),
                        (CONCAT(NEW.idcue, ".5"), NEW.idcue, 5, "9000");
                
                ELSEIF NEW.idcue LIKE "DISNEY%" THEN
                    INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
                        (CONCAT(NEW.idcue, ".1"), NEW.idcue, 1, "1000"),
                        (CONCAT(NEW.idcue, ".2"), NEW.idcue, 2, "5555"),
                        (CONCAT(NEW.idcue, ".3"), NEW.idcue, 3, "8833"),
                        (CONCAT(NEW.idcue, ".4"), NEW.idcue, 4, "6622"),
                        (CONCAT(NEW.idcue, ".5"), NEW.idcue, 5, "9000"),
                        (CONCAT(NEW.idcue, ".6"), NEW.idcue, 6, "2012"),
                        (CONCAT(NEW.idcue, ".7"), NEW.idcue, 7, "2000");
                
                ELSEIF NEW.idcue LIKE "PRIME%" THEN
                    INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
                        (CONCAT(NEW.idcue, ".1"), NEW.idcue, 1, "10000"),
                        (CONCAT(NEW.idcue, ".2"), NEW.idcue, 2, "55555"),
                        (CONCAT(NEW.idcue, ".3"), NEW.idcue, 3, "88333"),
                        (CONCAT(NEW.idcue, ".4"), NEW.idcue, 4, "66222"),
                        (CONCAT(NEW.idcue, ".5"), NEW.idcue, 5, "90000"),
                        (CONCAT(NEW.idcue, ".6"), NEW.idcue, 6, "20122");
                
                ELSEIF NEW.idcue LIKE "MAX%" THEN
                    INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
                        (CONCAT(NEW.idcue, ".1"), NEW.idcue, 1, "1000"),
                        (CONCAT(NEW.idcue, ".2"), NEW.idcue, 2, "5555"),
                        (CONCAT(NEW.idcue, ".3"), NEW.idcue, 3, "8833"),
                        (CONCAT(NEW.idcue, ".4"), NEW.idcue, 4, "6622"),
                        (CONCAT(NEW.idcue, ".5"), NEW.idcue, 5, "9000");
                
                ELSEIF NEW.idcue LIKE "PARAMOUNT%" THEN
                    INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
                        (CONCAT(NEW.idcue, ".1"), NEW.idcue, 1, "1000"),
                        (CONCAT(NEW.idcue, ".2"), NEW.idcue, 2, "5555"),
                        (CONCAT(NEW.idcue, ".3"), NEW.idcue, 3, "8833"),
                        (CONCAT(NEW.idcue, ".4"), NEW.idcue, 4, "6622"),
                        (CONCAT(NEW.idcue, ".5"), NEW.idcue, 5, "9000"),
                        (CONCAT(NEW.idcue, ".6"), NEW.idcue, 6, "2012");
                
                ELSEIF NEW.idcue LIKE "SPOTIFY%" THEN
                    INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
                        (CONCAT(NEW.idcue, ".1"), NEW.idcue, 1, "owner"),
                        (CONCAT(NEW.idcue, ".2"), NEW.idcue, 2, "invit"),
                        (CONCAT(NEW.idcue, ".3"), NEW.idcue, 3, "invit"),
                        (CONCAT(NEW.idcue, ".4"), NEW.idcue, 4, "invit"),
                        (CONCAT(NEW.idcue, ".5"), NEW.idcue, 5, "invit"),
                        (CONCAT(NEW.idcue, ".6"), NEW.idcue, 6, "invit");
                
                ELSEIF NEW.idcue LIKE "MAGIS%" THEN
                    INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
                        (CONCAT(NEW.idcue, ".1"), NEW.idcue, 1, ""),
                        (CONCAT(NEW.idcue, ".2"), NEW.idcue, 2, ""),
                        (CONCAT(NEW.idcue, ".3"), NEW.idcue, 3, "");
                
                ELSEIF NEW.idcue LIKE "CRUNCHY%" THEN
                    INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
                        (CONCAT(NEW.idcue, ".1"), NEW.idcue, 1, ""),
                        (CONCAT(NEW.idcue, ".2"), NEW.idcue, 2, ""),
                        (CONCAT(NEW.idcue, ".3"), NEW.idcue, 3, ""),
                        (CONCAT(NEW.idcue, ".4"), NEW.idcue, 4, ""),
                        (CONCAT(NEW.idcue, ".5"), NEW.idcue, 5, "");
                
                ELSEIF NEW.idcue LIKE "IND%" THEN
                    INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
                        (CONCAT(NEW.idcue, ".1"), NEW.idcue, 1, "aparte");
                
                ELSEIF NEW.idcue LIKE "COM%" THEN
                    INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
                        (CONCAT(NEW.idcue, ".1"), NEW.idcue, 1, "Cmplta");
                
                ELSE
                    INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
                        (CONCAT(NEW.idcue, ".1"), NEW.idcue, 1, "1111"),
                        (CONCAT(NEW.idcue, ".2"), NEW.idcue, 2, "2222"),
                        (CONCAT(NEW.idcue, ".3"), NEW.idcue, 3, "3333");
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS insertar_perfiles');
    }
};