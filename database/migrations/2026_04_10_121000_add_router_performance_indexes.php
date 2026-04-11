<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversaciones', function (Blueprint $table) {
            $table->index(
                ['canal_contacto_id', 'estado', 'ultima_actividad'],
                'conversaciones_contacto_estado_actividad_idx'
            );
        });

        Schema::table('mensajes', function (Blueprint $table) {
            $table->index(
                ['idconv', 'tipo_remitente', 'respondido_por_ai', 'created_at'],
                'mensajes_router_pendientes_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('conversaciones', function (Blueprint $table) {
            $table->dropIndex('conversaciones_contacto_estado_actividad_idx');
        });

        Schema::table('mensajes', function (Blueprint $table) {
            $table->dropIndex('mensajes_router_pendientes_idx');
        });
    }
};
