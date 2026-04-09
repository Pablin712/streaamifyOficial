<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_subagentes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 120);
            $table->enum('tipo', ['router', 'asistente', 'vendedor', 'soporte', 'cobranzas', 'postventa']);
            $table->text('descripcion')->nullable();
            $table->text('prompt_base')->nullable();
            $table->json('criterios')->nullable();
            $table->json('tools')->nullable();
            $table->unsignedSmallInteger('prioridad')->default(100);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('chat_memoria_negocio', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['faq', 'servicio', 'metodo_pago', 'politica_venta', 'politica_descuento', 'confianza', 'marca', 'objecion', 'guion']);
            $table->string('clave', 120);
            $table->string('titulo', 160);
            $table->text('contenido');
            $table->string('resumen', 255)->nullable();
            $table->enum('visibilidad', ['cliente', 'interna', 'ambas'])->default('cliente');
            $table->json('tags')->nullable();
            $table->string('fuente', 120)->nullable();
            $table->unsignedSmallInteger('prioridad')->default(100);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['tipo', 'clave'], 'chat_memoria_negocio_tipo_clave_unique');
            $table->index(['tipo', 'activo']);
            $table->index(['visibilidad', 'activo']);
        });

        Schema::create('chat_memoria_contactos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contacto_canal_id');
            $table->unsignedBigInteger('idcli')->nullable();
            $table->enum('tipo', ['perfil', 'preferencia', 'objecion', 'seguimiento', 'venta', 'pago', 'incidencia', 'contexto']);
            $table->string('clave', 120);
            $table->text('valor_texto')->nullable();
            $table->json('valor_json')->nullable();
            $table->enum('origen', ['sistema', 'ai', 'empleado', 'cliente'])->default('sistema');
            $table->decimal('confianza', 5, 2)->nullable();
            $table->timestamp('vigente_hasta')->nullable();
            $table->timestamp('ultima_referencia_at')->nullable();
            $table->timestamps();

            $table->index(['contacto_canal_id', 'tipo']);
            $table->index(['idcli', 'tipo']);
            $table->index('vigente_hasta');

            $table->foreign('contacto_canal_id')
                ->references('id')
                ->on('chat_contactos_canal')
                ->cascadeOnDelete();

            $table->foreign('idcli')
                ->references('idcli')
                ->on('clientes')
                ->nullOnDelete();
        });

        Schema::create('chat_memoria_resumenes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idconv')->nullable();
            $table->unsignedBigInteger('contacto_canal_id')->nullable();
            $table->unsignedBigInteger('idcli')->nullable();
            $table->unsignedBigInteger('subagente_id')->nullable();
            $table->enum('tipo', ['conversacion', 'cliente', 'handoff', 'followup'])->default('conversacion');
            $table->timestamp('ventana_desde')->nullable();
            $table->timestamp('ventana_hasta')->nullable();
            $table->text('resumen');
            $table->json('hechos_clave')->nullable();
            $table->timestamp('expira_at')->nullable();
            $table->timestamps();

            $table->index(['contacto_canal_id', 'tipo']);
            $table->index(['idconv', 'tipo']);
            $table->index('expira_at');

            $table->foreign('idconv')
                ->references('idconv')
                ->on('conversaciones')
                ->nullOnDelete();

            $table->foreign('contacto_canal_id')
                ->references('id')
                ->on('chat_contactos_canal')
                ->nullOnDelete();

            $table->foreign('idcli')
                ->references('idcli')
                ->on('clientes')
                ->nullOnDelete();

            $table->foreign('subagente_id')
                ->references('id')
                ->on('chat_subagentes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_memoria_resumenes');
        Schema::dropIfExists('chat_memoria_contactos');
        Schema::dropIfExists('chat_memoria_negocio');
        Schema::dropIfExists('chat_subagentes');
    }
};
