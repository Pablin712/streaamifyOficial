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
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->string('nombretarea', 255);
            $table->text('descripcion')->nullable();
            $table->enum('prioridad', ['alta', 'media', 'baja'])->default('baja');
            $table->boolean('completada')->default(0);
            $table->dateTime('fechalimit')->nullable();
            $table->unsignedBigInteger('completada_por')->nullable();
            $table->dateTime('fecha_completada')->nullable();
            $table->timestamps();
            
            $table->foreign('completada_por')->references('idemp')->on('empleados')->onDelete('set null');
        });

        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empleado_id');
            $table->string('ruta_actual', 255);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            
            $table->foreign('empleado_id')->references('idemp')->on('empleados')->onDelete('cascade');
        });

        Schema::create('historial', function (Blueprint $table) {
            $table->id();
            $table->string('accion', 255);
            $table->text('descripcion')->nullable();
            $table->bigInteger('empleado_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('mails', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->string('host', 20)->default('Hostinger');
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('type', 255);
            $table->string('notifiable_type', 255);
            $table->unsignedBigInteger('notifiable_id');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['notifiable_type', 'notifiable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('mails');
        Schema::dropIfExists('historial');
        Schema::dropIfExists('asistencias');
        Schema::dropIfExists('tareas');
    }
};