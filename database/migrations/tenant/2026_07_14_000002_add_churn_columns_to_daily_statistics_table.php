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
        // Métricas de rotación de clientes (churn):
        // - usuarios_removidos: veces que se quitó un usuario/perfil ese día
        // - clientes_perdidos: clientes que quedaron sin usuarios activos (usuarios=0) ese día
        Schema::table('daily_statistics', function (Blueprint $table) {
            $table->unsignedInteger('usuarios_removidos')->default(0)->after('new_customers');
            $table->unsignedInteger('clientes_perdidos')->default(0)->after('usuarios_removidos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_statistics', function (Blueprint $table) {
            $table->dropColumn(['usuarios_removidos', 'clientes_perdidos']);
        });
    }
};
