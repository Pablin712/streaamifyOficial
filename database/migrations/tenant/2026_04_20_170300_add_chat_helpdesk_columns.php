<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('conversaciones', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable()->after('ultimo_idemp');
                $table->foreign('assigned_to')
                    ->references('idemp')
                    ->on('empleados')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('conversaciones', 'unread_count')) {
                $table->unsignedInteger('unread_count')->default(0)->after('mensajes_no_leidos');
            }

            if (!Schema::hasColumn('conversaciones', 'prioridad')) {
                $table->enum('prioridad', ['baja', 'normal', 'alta', 'urgente'])->default('normal')->after('unread_count');
            }

            if (!Schema::hasColumn('conversaciones', 'last_message_at')) {
                $table->timestamp('last_message_at')->nullable()->after('ultima_actividad');
            }

            if (!Schema::hasColumn('conversaciones', 'operator_typing_id')) {
                $table->unsignedBigInteger('operator_typing_id')->nullable()->after('assigned_to');
                $table->timestamp('operator_typing_at')->nullable()->after('operator_typing_id');
                $table->foreign('operator_typing_id')
                    ->references('idemp')
                    ->on('empleados')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('conversaciones', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('last_message_at');
            }
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE conversaciones MODIFY COLUMN estado ENUM('nueva', 'nuevo', 'abierta', 'abierto', 'asignado', 'atendiendo', 'pausado', 'cerrado', 'en_atencion', 'en_espera', 'cerrada', 'bot_activo') NOT NULL DEFAULT 'nueva'");
        }

        Schema::table('conversaciones', function (Blueprint $table) {
            if (Schema::hasColumn('conversaciones', 'assigned_to')) {
                $table->index(['assigned_to', 'estado'], 'chat_conv_assigned_estado_idx');
            }
            if (Schema::hasColumn('conversaciones', 'last_message_at')) {
                $table->index('last_message_at', 'chat_conv_last_message_idx');
            }
        });

        Schema::table('mensajes', function (Blueprint $table) {
            if (!Schema::hasColumn('mensajes', 'tipo')) {
                $table->string('tipo', 30)->nullable()->after('tipo_contenido');
            }

            if (!Schema::hasColumn('mensajes', 'media_url')) {
                $table->text('media_url')->nullable()->after('archivo_url');
            }

            if (!Schema::hasColumn('mensajes', 'mime_type')) {
                $table->string('mime_type', 120)->nullable()->after('media_url');
            }

            if (!Schema::hasColumn('mensajes', 'external_id')) {
                $table->string('external_id', 191)->nullable()->after('mime_type');
            }

            if (!Schema::hasColumn('mensajes', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('external_id');
            }

            if (!Schema::hasColumn('mensajes', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('delivered_at');
            }

            if (!Schema::hasColumn('mensajes', 'error_message')) {
                $table->text('error_message')->nullable()->after('read_at');
            }
        });

        Schema::table('mensajes', function (Blueprint $table) {
            if (Schema::hasColumn('mensajes', 'external_id')) {
                $table->index('external_id', 'chat_msg_external_id_idx');
            }
            if (Schema::hasColumn('mensajes', 'tipo')) {
                $table->index(['idconv', 'tipo', 'created_at'], 'chat_msg_conv_tipo_created_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mensajes', function (Blueprint $table) {
            if (Schema::hasColumn('mensajes', 'external_id')) {
                $table->dropIndex('chat_msg_external_id_idx');
            }
            if (Schema::hasColumn('mensajes', 'tipo')) {
                $table->dropIndex('chat_msg_conv_tipo_created_idx');
            }
            $table->dropColumn([
                'tipo',
                'media_url',
                'mime_type',
                'external_id',
                'delivered_at',
                'read_at',
                'error_message',
            ]);
        });

        Schema::table('conversaciones', function (Blueprint $table) {
            if (Schema::hasColumn('conversaciones', 'assigned_to')) {
                $table->dropIndex('chat_conv_assigned_estado_idx');
            }
            if (Schema::hasColumn('conversaciones', 'last_message_at')) {
                $table->dropIndex('chat_conv_last_message_idx');
            }
            if (Schema::hasColumn('conversaciones', 'assigned_to')) {
                $table->dropForeign(['assigned_to']);
            }
            if (Schema::hasColumn('conversaciones', 'operator_typing_id')) {
                $table->dropForeign(['operator_typing_id']);
            }
            $table->dropColumn([
                'assigned_to',
                'unread_count',
                'prioridad',
                'last_message_at',
                'operator_typing_id',
                'operator_typing_at',
                'closed_at',
            ]);
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE conversaciones MODIFY COLUMN estado ENUM('abierta', 'en_atencion', 'en_espera', 'cerrada', 'bot_activo') NOT NULL DEFAULT 'abierta'");
        }
    }
};

