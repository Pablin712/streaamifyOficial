<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_gasto', function (Blueprint $table) {
            $table->boolean('excluir_de_ganancia')->default(false)->after('detalletip');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_gasto', function (Blueprint $table) {
            $table->dropColumn('excluir_de_ganancia');
        });
    }
};
