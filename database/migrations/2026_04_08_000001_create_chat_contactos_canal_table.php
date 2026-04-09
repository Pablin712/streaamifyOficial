<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_contactos_canal', function (Blueprint $table) {
            $table->id();
            $table->enum('canal', ['whatsapp', 'messenger', 'telegram', 'webchat']);
            $table->string('canal_user_id', 120);
            $table->string('telefono_normalizado', 30)->nullable();
            $table->string('nombre_canal', 120)->nullable();
            $table->unsignedBigInteger('idcli')->nullable();
            $table->enum('estado_relacion', ['lead', 'vinculado', 'cliente', 'bloqueado'])->default('lead');
            $table->string('origen', 50)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['canal', 'canal_user_id'], 'chat_contactos_canal_unique');
            $table->index('telefono_normalizado');
            $table->index('idcli');
            $table->index(['canal', 'last_seen_at']);

            $table->foreign('idcli')
                ->references('idcli')
                ->on('clientes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_contactos_canal');
    }
};
