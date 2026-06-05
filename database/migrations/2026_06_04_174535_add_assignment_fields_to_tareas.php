<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            // Quién tiene asignada la tarea (null = pool disponible)
            $table->unsignedBigInteger('assignee_id')->nullable()->after('completada_por');
            // Quién la asignó (admin o el mismo empleado si se auto-asignó)
            $table->unsignedBigInteger('asignado_por')->nullable()->after('assignee_id');
            $table->timestamp('assigned_at')->nullable()->after('asignado_por');

            // Referencia al registro específico que origina la tarea
            $table->string('tipo_tarea', 60)->nullable()->after('assigned_at');    // cobrar_usuario, quitar_usuario…
            $table->string('related_model', 60)->nullable()->after('tipo_tarea');  // ViewUsuarioActivo, Cuenta, Soporte
            $table->string('related_id', 120)->nullable()->after('related_model'); // string porque idcue es varchar

            $table->foreign('assignee_id')->references('idemp')->on('empleados')->nullOnDelete();
            $table->foreign('asignado_por')->references('idemp')->on('empleados')->nullOnDelete();

            $table->index(['assignee_id', 'completada']);
            $table->index(['tipo_tarea', 'completada']);
            $table->index(['related_model', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->dropForeign(['assignee_id']);
            $table->dropForeign(['asignado_por']);
            $table->dropIndex(['tareas_assignee_id_completada_index']);
            $table->dropIndex(['tareas_tipo_tarea_completada_index']);
            $table->dropIndex(['tareas_related_model_related_id_index']);
            $table->dropColumn(['assignee_id', 'asignado_por', 'assigned_at', 'tipo_tarea', 'related_model', 'related_id']);
        });
    }
};
