<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_memoria_negocio', function (Blueprint $table) {
            $table->string('categoria', 60)->default('general')->after('tipo');
        });

        DB::statement("
            ALTER TABLE chat_memoria_negocio
            MODIFY COLUMN tipo ENUM(
                'faq','servicio','metodo_pago','politica_venta',
                'politica_descuento','confianza','marca','objecion',
                'guion','campaña','soporte_pasos','soporte_escalado'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('chat_memoria_negocio', function (Blueprint $table) {
            $table->dropColumn('categoria');
        });

        DB::statement("
            ALTER TABLE chat_memoria_negocio
            MODIFY COLUMN tipo ENUM(
                'faq','servicio','metodo_pago','politica_venta',
                'politica_descuento','confianza','marca','objecion',
                'guion','campaña'
            ) NOT NULL
        ");
    }
};
