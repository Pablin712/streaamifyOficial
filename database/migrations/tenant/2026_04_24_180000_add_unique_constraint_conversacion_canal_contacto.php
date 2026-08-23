<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Paso 1: Encontrar y consolidar conversaciones duplicadas
        $duplicates = DB::table('conversaciones')
            ->select('canal_contacto_id', DB::raw('COUNT(*) as count'))
            ->groupBy('canal_contacto_id')
            ->having(DB::raw('count(*)'), '>', 1)
            ->where('canal_contacto_id', '!=', null)
            ->get();

        foreach ($duplicates as $dup) {
            // Obtener todas las conversaciones para este contacto
            $conversations = DB::table('conversaciones')
                ->where('canal_contacto_id', $dup->canal_contacto_id)
                ->orderBy('last_message_at', 'desc')
                ->get();

            if ($conversations->count() > 1) {
                // Mantener la más reciente
                $keep = $conversations->first();

                // Eliminar las antiguas (sus mensajes ya están asociados)
                foreach ($conversations->skip(1) as $old) {
                    // IMPORTANTE: Reasignar mensajes de conversación vieja a la nueva
                    DB::table('mensajes')
                        ->where('idconv', $old->idconv)
                        ->update(['idconv' => $keep->idconv]);

                    DB::table('chat_mensajes_canal')
                        ->where('idconv', $old->idconv)
                        ->update(['idconv' => $keep->idconv]);

                    // Eliminar la conversación duplicada
                    DB::table('conversaciones')
                        ->where('idconv', $old->idconv)
                        ->delete();
                }
            }
        }

        // Paso 2: Agregar constraint UNIQUE
        Schema::table('conversaciones', function (Blueprint $table) {
            // Ahora que no hay duplicados, agregar constraint
            $table->unique('canal_contacto_id', 'idx_conversacion_contacto_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversaciones', function (Blueprint $table) {
            $table->dropUnique('idx_conversacion_contacto_unique');
        });
    }
};
