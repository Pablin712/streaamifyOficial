<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_contactos_canal', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_contactos_canal', 'telefono')) {
                $table->string('telefono', 50)->nullable()->after('telefono_normalizado');
            }

            if (! Schema::hasColumn('chat_contactos_canal', 'nombre')) {
                $table->string('nombre', 191)->nullable()->after('nombre_canal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chat_contactos_canal', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('chat_contactos_canal', 'telefono') ? 'telefono' : null,
                Schema::hasColumn('chat_contactos_canal', 'nombre') ? 'nombre' : null,
            ]));
        });
    }
};
