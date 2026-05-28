<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si la tabla no existe, la creamos completa (entorno de producción sin migraciones previas)
        if (!Schema::hasTable('donna_channels')) {
            Schema::create('donna_channels', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id');
                $table->unsignedBigInteger('subscription_id')->nullable();
                $table->enum('service_type', ['personal', 'business']);
                $table->enum('channel_type', ['whatsapp', 'telegram']);
                $table->string('provider', 50);
                $table->string('instance_name', 100)->nullable();
                $table->string('phone_number', 50)->nullable();
                $table->string('owner_identifier', 100)->nullable();
                $table->string('telegram_username', 100)->nullable();
                $table->string('telegram_name', 150)->nullable();
                $table->timestamp('activated_at')->nullable();
                $table->text('api_key_encrypted')->nullable();
                $table->string('api_base_url', 255)->nullable();
                $table->string('webhook_url', 255)->nullable();
                $table->enum('status', ['pending', 'active', 'inactive', 'error', 'suspended'])->default('pending');
                $table->string('activation_code', 20)->nullable()->unique();
                $table->boolean('is_default')->default(true);
                $table->timestamp('last_connected_at')->nullable();
                $table->text('last_error')->nullable();
                $table->json('metadata_json')->nullable();
                $table->timestamps();

                $table->foreign('client_id')->references('idcli')->on('clientes')->onDelete('cascade');
                $table->foreign('subscription_id')->references('id')->on('donna_subscriptions')->onDelete('set null');
            });
            return; // Todos los campos ya están incluidos arriba
        }

        // Tabla ya existe (entorno local) — solo agrega la columna si falta
        if (!Schema::hasColumn('donna_channels', 'activation_code')) {
            Schema::table('donna_channels', function (Blueprint $table) {
                $table->string('activation_code', 20)->nullable()->unique()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('donna_channels')) {
            Schema::table('donna_channels', function (Blueprint $table) {
                if (Schema::hasColumn('donna_channels', 'activation_code')) {
                    $table->dropColumn('activation_code');
                }
            });
        }
    }
};
