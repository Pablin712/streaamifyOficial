<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversaciones', function (Blueprint $table) {
            $table->dropForeign(['idcli']);
        });

        Schema::table('conversaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('idcli')->nullable()->change();
            $table->enum('canal_principal', ['whatsapp', 'messenger', 'telegram', 'webchat'])->nullable()->after('idcli');
            $table->unsignedBigInteger('canal_contacto_id')->nullable()->after('canal_principal');
            $table->string('origen', 50)->nullable()->after('canal_contacto_id');
            $table->string('subagente_codigo', 50)->nullable()->after('origen');

            $table->index(['canal_principal', 'estado'], 'conversaciones_canal_estado_index');
            $table->index('canal_contacto_id');

            $table->foreign('idcli')
                ->references('idcli')
                ->on('clientes')
                ->nullOnDelete();

            $table->foreign('canal_contacto_id')
                ->references('id')
                ->on('chat_contactos_canal')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('conversaciones', function (Blueprint $table) {
            $table->dropForeign(['canal_contacto_id']);
            $table->dropForeign(['idcli']);
            $table->dropIndex('conversaciones_canal_estado_index');
            $table->dropIndex(['canal_contacto_id']);
            $table->dropColumn(['canal_principal', 'canal_contacto_id', 'origen', 'subagente_codigo']);
            $table->unsignedBigInteger('idcli')->nullable(false)->change();

            $table->foreign('idcli')
                ->references('idcli')
                ->on('clientes')
                ->cascadeOnDelete();
        });
    }
};
