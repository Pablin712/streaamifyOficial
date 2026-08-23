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
        Schema::table('mensajes', function (Blueprint $table) {
            $table->boolean('respondido_por_ai')->default(false)->after('leido_at');
            $table->index('respondido_por_ai'); // Para búsquedas rápidas de mensajes sin responder
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mensajes', function (Blueprint $table) {
            $table->dropIndex(['respondido_por_ai']);
            $table->dropColumn('respondido_por_ai');
        });
    }
};
