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
            DB::statement("
                CREATE VIEW ventas_mensuales AS
                SELECT
                    EXTRACT(YEAR FROM fechaven) AS anio,
                    EXTRACT(MONTH FROM fechaven) AS mes,
                    COUNT(*) AS total_ventas,
                    SUM(totalpagoven) AS total_monto
                FROM ventas
                GROUP BY anio, mes
                ORDER BY anio DESC, mes DESC
            ");
            DB::statement("
                CREATE VIEW usuarios_activos_mensuales AS
                SELECT
                    EXTRACT(YEAR FROM date) AS anio,
                    EXTRACT(MONTH FROM date) AS mes,
                    ROUND(AVG(active_users))::INT AS promedio_usuarios_activos
                FROM daily_statistics
                GROUP BY anio, mes
                ORDER BY anio DESC, mes DESC;
            ");
        } elseif ($driver === 'mysql') {
            // MySQL
            DB::statement("
                CREATE VIEW ventas_mensuales AS
                SELECT
                    YEAR(fechaven) AS anio,
                    MONTH(fechaven) AS mes,
                    COUNT(*) AS total_ventas,
                    SUM(totalpagoven) AS total_monto
                FROM ventas
                GROUP BY anio, mes
                ORDER BY anio DESC, mes DESC
            ");
            DB::statement("
                CREATE VIEW usuarios_activos_mensuales AS
                SELECT
                    YEAR(date) AS anio,
                    MONTH(date) AS mes,
                    ROUND(AVG(active_users)) AS promedio_usuarios_activos
                FROM daily_statistics
                GROUP BY anio, mes
                ORDER BY anio DESC, mes DESC;
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
        DB::statement("DROP VIEW IF EXISTS ventas_mensuales");
    }
};
