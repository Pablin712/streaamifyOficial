<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('whatsapp_analisis_conversacion', function (Blueprint $table) {
            // Servicio (Netflix, Disney+, etc.) sobre el que trata la conversacion,
            // clasificado por la IA. Null si la conversacion no es sobre un servicio
            // puntual identificable (ej. consulta general).
            $table->string('servicio_idser', 20)->nullable()->after('empleado_principal_idemp');

            // Motivo de contacto del cliente, clasificado por la IA. Para inteligencia
            // de negocio: cuantos contactos por servicio son soporte vs codigo vs venta.
            $table->enum('motivo_contacto', [
                'soporte_tecnico',
                'solicitar_codigo',
                'compra',
                'renovacion',
                'consulta_general',
                'otro',
            ])->default('otro')->after('servicio_idser');

            $table->foreign('servicio_idser')->references('idser')->on('servicios')->nullOnDelete();
            $table->index(['servicio_idser', 'fecha_conversacion'], 'wac_servicio_fecha_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_analisis_conversacion', function (Blueprint $table) {
            $table->dropForeign(['servicio_idser']);
            $table->dropIndex('wac_servicio_fecha_idx');
            $table->dropColumn(['servicio_idser', 'motivo_contacto']);
        });
    }
};
