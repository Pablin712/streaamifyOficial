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
            -- Crear la función INSERTAR PERFILES
            CREATE OR REPLACE FUNCTION insertar_perfiles()
            RETURNS TRIGGER AS $$
            BEGIN
                -- Insertar perfiles para Netflix
                IF NEW.IDCUE LIKE 'NETFLIX%' THEN
                    INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
                    (NEW.IDCUE || '.1', NEW.IDCUE, 1, '1000'),
                    (NEW.IDCUE || '.2', NEW.IDCUE, 2, '5555'),
                    (NEW.IDCUE || '.3', NEW.IDCUE, 3, '8833'),
                    (NEW.IDCUE || '.4', NEW.IDCUE, 4, '6622'),
                    (NEW.IDCUE || '.5', NEW.IDCUE, 5, '9000');
                END IF;
                -- Insertar perfiles para Disney
                IF NEW.IDCUE LIKE 'DISNEY%' THEN
                    INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
                    (NEW.IDCUE || '.1', NEW.IDCUE, 1, '1000'),
                    (NEW.IDCUE || '.2', NEW.IDCUE, 2, '5555'),
                    (NEW.IDCUE || '.3', NEW.IDCUE, 3, '8833'),
                    (NEW.IDCUE || '.4', NEW.IDCUE, 4, '6622'),
                    (NEW.IDCUE || '.5', NEW.IDCUE, 5, '9000'),
                    (NEW.IDCUE || '.6', NEW.IDCUE, 6, '2012'),
                    (NEW.IDCUE || '.7', NEW.IDCUE, 7, '2000');
                END IF;
                -- Insertar perfiles para Prime Video
                IF NEW.IDCUE LIKE 'PRIME%' THEN
                    INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
                    (NEW.IDCUE || '.1', NEW.IDCUE, 1, '10000'),
                    (NEW.IDCUE || '.2', NEW.IDCUE, 2, '55555'),
                    (NEW.IDCUE || '.3', NEW.IDCUE, 3, '88333'),
                    (NEW.IDCUE || '.4', NEW.IDCUE, 4, '66222'),
                    (NEW.IDCUE || '.5', NEW.IDCUE, 5, '90000'),
                    (NEW.IDCUE || '.6', NEW.IDCUE, 6, '20122');
                END IF;
                -- Insertar perfiles para Max
                IF NEW.IDCUE LIKE 'MAX%' THEN
                    INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
                    (NEW.IDCUE || '.1', NEW.IDCUE, 1, '1000'),
                    (NEW.IDCUE || '.2', NEW.IDCUE, 2, '5555'),
                    (NEW.IDCUE || '.3', NEW.IDCUE, 3, '8833'),
                    (NEW.IDCUE || '.4', NEW.IDCUE, 4, '6622'),
                    (NEW.IDCUE || '.5', NEW.IDCUE, 5, '9000');
                END IF;
                -- Insertar perfiles para Paramount
                IF NEW.IDCUE LIKE 'PARAMOUNT%' THEN
                    INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
                    (NEW.IDCUE || '.1', NEW.IDCUE, 1, '1000'),
                    (NEW.IDCUE || '.2', NEW.IDCUE, 2, '5555'),
                    (NEW.IDCUE || '.3', NEW.IDCUE, 3, '8833'),
                    (NEW.IDCUE || '.4', NEW.IDCUE, 4, '6622'),
                    (NEW.IDCUE || '.5', NEW.IDCUE, 5, '9000'),
                    (NEW.IDCUE || '.6', NEW.IDCUE, 6, '2012');
                END IF;
                -- Insertar perfiles para Spotify
                IF NEW.IDCUE LIKE 'SPOTIFY%' THEN
                    INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
                    (NEW.IDCUE || '.1', NEW.IDCUE, 1, 'owner'),
                    (NEW.IDCUE || '.2', NEW.IDCUE, 2, 'invit'),
                    (NEW.IDCUE || '.3', NEW.IDCUE, 3, 'invit'),
                    (NEW.IDCUE || '.4', NEW.IDCUE, 4, 'invit'),
                    (NEW.IDCUE || '.5', NEW.IDCUE, 5, 'invit'),
                    (NEW.IDCUE || '.6', NEW.IDCUE, 6, 'invit');
                END IF;
                IF NEW.IDCUE LIKE 'MAGIS%' THEN
                    INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
                    (NEW.IDCUE || '.1', NEW.IDCUE, 1, ''),
                    (NEW.IDCUE || '.2', NEW.IDCUE, 2, ''),
                    (NEW.IDCUE || '.3', NEW.IDCUE, 3, '');
                END IF;
                IF NEW.IDCUE LIKE 'CRUNCHY%' THEN
                    INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
                    (NEW.IDCUE || '.1', NEW.IDCUE, 1, ''),
                    (NEW.IDCUE || '.2', NEW.IDCUE, 2, ''),
                    (NEW.IDCUE || '.3', NEW.IDCUE, 3, ''),
                    (NEW.IDCUE || '.4', NEW.IDCUE, 4, ''),
                    (NEW.IDCUE || '.5', NEW.IDCUE, 5, '');
                END IF;
                IF NEW.IDCUE LIKE 'ind%' THEN
                    INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
                    (NEW.IDCUE || '.1', NEW.IDCUE, 1, 'aparte');
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
            -- Crear el trigger
            CREATE OR REPLACE TRIGGER TG_insertar_perfiles
            AFTER INSERT ON cuentas
            FOR EACH ROW
            EXECUTE FUNCTION insertar_perfiles();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS TG_insertar_perfiles ON Cuentas;
            DROP FUNCTION IF EXISTS insertar_perfiles;
        ");
    }
};
