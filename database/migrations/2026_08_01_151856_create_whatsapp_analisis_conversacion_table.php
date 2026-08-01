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
        Schema::create('whatsapp_analisis_conversacion', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('idconv')->unique();
            $table->unsignedBigInteger('idcli')->nullable();

            // Empleados que participaron en la conversacion (idemp[]), y el principal
            // (para Fase 2: generar la Tarea sintetica "whatsapp_calidad" a su nombre).
            $table->json('empleados_involucrados')->nullable();
            $table->unsignedBigInteger('empleado_principal_idemp')->nullable();

            // Metricas deterministicas (calculadas en PHP, no por la IA).
            $table->unsignedInteger('mensajes_cliente_count')->default(0);
            $table->boolean('respondido')->default(false);
            $table->unsignedInteger('tiempo_respuesta_promedio_segundos')->nullable();

            // Juicios de la IA (requieren lectura semantica de la conversacion).
            $table->unsignedTinyInteger('satisfaccion_score')->nullable();
            $table->text('satisfaccion_justificacion')->nullable();
            $table->enum('motivo_perdida', [
                'no_aplica',
                'mala_atencion',
                'sin_respuesta',
                'sin_presupuesto',
                'no_continuara',
                'otro_servicio',
                'otro',
            ])->default('no_aplica');
            $table->enum('cruce_empleados', ['ninguno', 'tardado', 'dividido'])->default('ninguno');
            $table->text('cruce_detalle')->nullable();

            // Snapshot de la fecha de cierre de la conversacion, para poder agrupar
            // por dia/semana/mes sin tener que joinear contra conversaciones.
            $table->date('fecha_conversacion')->nullable();

            // Auditoria del analisis en si (que modelo respondio y su JSON crudo).
            $table->string('modelo_ia', 60)->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamp('analizado_en')->nullable();

            $table->timestamps();

            $table->foreign('idconv')->references('idconv')->on('conversaciones')->cascadeOnDelete();
            $table->foreign('idcli')->references('idcli')->on('clientes')->nullOnDelete();
            $table->foreign('empleado_principal_idemp')->references('idemp')->on('empleados')->nullOnDelete();

            $table->index('fecha_conversacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_analisis_conversacion');
    }
};
