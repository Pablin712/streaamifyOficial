<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recargas', function (Blueprint $table) {
            $table->string('comprobante_hash', 64)->nullable()->after('foto');
            $table->string('origen', 30)->nullable()->after('idban');
            $table->string('external_reference', 191)->nullable()->after('origen');
            $table->json('metadata')->nullable()->after('external_reference');

            $table->unique('comprobante_hash', 'recargas_comprobante_hash_unique');
            $table->index(['idcli', 'created_at'], 'recargas_cliente_fecha_idx');
            $table->index('external_reference', 'recargas_external_reference_idx');
        });
    }

    public function down(): void
    {
        Schema::table('recargas', function (Blueprint $table) {
            $table->dropUnique('recargas_comprobante_hash_unique');
            $table->dropIndex('recargas_cliente_fecha_idx');
            $table->dropIndex('recargas_external_reference_idx');
            $table->dropColumn(['comprobante_hash', 'origen', 'external_reference', 'metadata']);
        });
    }
};
