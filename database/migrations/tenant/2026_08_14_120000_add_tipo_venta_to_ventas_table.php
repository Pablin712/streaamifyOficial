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
        Schema::table('ventas', function (Blueprint $table) {
            // nueva: primera compra del cliente.
            // renovacion: extiende un perfil que ya tenía (storeRenew).
            // ampliacion: cliente con algo vigente compra un perfil adicional.
            // reactivacion: cliente sin nada vigente vuelve a comprar.
            // Nullable: se llena hacia adelante en VentaController; el historico
            // se completa con el comando ventas:clasificar-tipo (best-effort).
            $table->enum('tipo_venta', ['nueva', 'renovacion', 'ampliacion', 'reactivacion'])
                ->nullable()
                ->after('totalpagoven');
            $table->index('tipo_venta', 'ventas_tipo_venta_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('ventas_tipo_venta_idx');
            $table->dropColumn('tipo_venta');
        });
    }
};
