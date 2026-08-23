<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donna_channels', function (Blueprint $table) {
            $table->enum('donna_mode', ['production', 'test'])->default('production')->after('status');
            // Modo prueba: lista blanca de números que Donna sí responde.
            $table->json('test_numbers_json')->nullable()->after('donna_mode');
            // Modo producción: lista negra de números específicos que Donna NO responde.
            $table->json('excluded_numbers_json')->nullable()->after('test_numbers_json');
            // Modo producción: si es true, ignora automáticamente cualquier chat de grupo (JID *@g.us).
            $table->boolean('exclude_groups_in_production')->default(true)->after('excluded_numbers_json');
        });
    }

    public function down(): void
    {
        Schema::table('donna_channels', function (Blueprint $table) {
            $table->dropColumn([
                'donna_mode',
                'test_numbers_json',
                'excluded_numbers_json',
                'exclude_groups_in_production',
            ]);
        });
    }
};
