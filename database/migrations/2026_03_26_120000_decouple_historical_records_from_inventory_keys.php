<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->string('idper_snapshot', 50)->nullable()->after('idper');
            $table->string('idcue_snapshot', 50)->nullable()->after('idper_snapshot');
            $table->string('idval_snapshot', 30)->nullable()->after('idcue_snapshot');
            $table->string('servicio_snapshot', 50)->nullable()->after('idval_snapshot');
            $table->string('cuenta_usuario_snapshot', 100)->nullable()->after('servicio_snapshot');
            $table->integer('perfil_numeroper_snapshot')->nullable()->after('cuenta_usuario_snapshot');
        });

        Schema::table('costos', function (Blueprint $table) {
            $table->string('idcue_snapshot', 50)->nullable()->after('idcue');
            $table->string('idval_snapshot', 30)->nullable()->after('idcue_snapshot');
            $table->string('servicio_snapshot', 50)->nullable()->after('idval_snapshot');
            $table->string('cuenta_usuario_snapshot', 100)->nullable()->after('servicio_snapshot');
        });

        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->string('idcue_snapshot', 50)->nullable()->after('idcue');
            $table->string('idval_snapshot', 30)->nullable()->after('idcue_snapshot');
            $table->string('servicio_snapshot', 50)->nullable()->after('idval_snapshot');
            $table->string('cuenta_usuario_snapshot', 100)->nullable()->after('servicio_snapshot');
        });

        DB::statement("UPDATE detalles_venta dv
            LEFT JOIN perfiles p ON dv.idper = p.idper
            LEFT JOIN cuentas c ON p.idcue = c.idcue
            LEFT JOIN valores v ON c.idval = v.idval
            LEFT JOIN servicios s ON v.idser = s.idser
            SET
                dv.idper_snapshot = COALESCE(dv.idper_snapshot, dv.idper),
                dv.idcue_snapshot = COALESCE(dv.idcue_snapshot, c.idcue),
                dv.idval_snapshot = COALESCE(dv.idval_snapshot, v.idval),
                dv.servicio_snapshot = COALESCE(dv.servicio_snapshot, s.nombreser, v.idser),
                dv.cuenta_usuario_snapshot = COALESCE(dv.cuenta_usuario_snapshot, c.usuariocue),
                dv.perfil_numeroper_snapshot = COALESCE(dv.perfil_numeroper_snapshot, p.numeroper)
            WHERE dv.idper IS NOT NULL");

        DB::statement("UPDATE costos cos
            LEFT JOIN cuentas c ON cos.idcue = c.idcue
            LEFT JOIN valores v ON c.idval = v.idval
            LEFT JOIN servicios s ON v.idser = s.idser
            SET
                cos.idcue_snapshot = COALESCE(cos.idcue_snapshot, c.idcue),
                cos.idval_snapshot = COALESCE(cos.idval_snapshot, v.idval),
                cos.servicio_snapshot = COALESCE(cos.servicio_snapshot, s.nombreser, v.idser),
                cos.cuenta_usuario_snapshot = COALESCE(cos.cuenta_usuario_snapshot, c.usuariocue)
            WHERE cos.idcue IS NOT NULL");

        DB::statement("UPDATE mantenimientos man
            LEFT JOIN cuentas c ON man.idcue = c.idcue
            LEFT JOIN valores v ON c.idval = v.idval
            LEFT JOIN servicios s ON v.idser = s.idser
            SET
                man.idcue_snapshot = COALESCE(man.idcue_snapshot, c.idcue),
                man.idval_snapshot = COALESCE(man.idval_snapshot, v.idval),
                man.servicio_snapshot = COALESCE(man.servicio_snapshot, s.nombreser, v.idser),
                man.cuenta_usuario_snapshot = COALESCE(man.cuenta_usuario_snapshot, c.usuariocue)
            WHERE man.idcue IS NOT NULL");

        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->dropForeign(['idper']);
        });

        Schema::table('costos', function (Blueprint $table) {
            $table->dropForeign(['idcue']);
        });

        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropForeign(['idcue']);
        });

        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->string('idcue', 50)->nullable()->change();
        });

        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->foreign('idper')->references('idper')->on('perfiles')->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::table('costos', function (Blueprint $table) {
            $table->foreign('idcue')->references('idcue')->on('cuentas')->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->foreign('idcue')->references('idcue')->on('cuentas')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->dropForeign(['idper']);
        });

        Schema::table('costos', function (Blueprint $table) {
            $table->dropForeign(['idcue']);
        });

        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropForeign(['idcue']);
        });

        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->foreign('idper')->references('idper')->on('perfiles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dropColumn([
                'idper_snapshot',
                'idcue_snapshot',
                'idval_snapshot',
                'servicio_snapshot',
                'cuenta_usuario_snapshot',
                'perfil_numeroper_snapshot',
            ]);
        });

        Schema::table('costos', function (Blueprint $table) {
            $table->foreign('idcue')->references('idcue')->on('cuentas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dropColumn([
                'idcue_snapshot',
                'idval_snapshot',
                'servicio_snapshot',
                'cuenta_usuario_snapshot',
            ]);
        });

        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->foreign('idcue')->references('idcue')->on('cuentas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dropColumn([
                'idcue_snapshot',
                'idval_snapshot',
                'servicio_snapshot',
                'cuenta_usuario_snapshot',
            ]);
        });
    }
};
