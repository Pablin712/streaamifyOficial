<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDuplicates extends Command
{
    protected $signature = 'check:duplicates';
    protected $description = 'Check for duplicate conversations';

    public function handle()
    {
        $duplicates = DB::table('conversaciones')
            ->select('canal_contacto_id', DB::raw('COUNT(*) as count'))
            ->groupBy('canal_contacto_id')
            ->having(DB::raw('count(*)'), '>', 1)
            ->where('canal_contacto_id', '!=', null)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('✅ NO HAY DUPLICADOS - Sistema limpio');
            $this->line('');
            $this->line('Total de conversaciones: ' . DB::table('conversaciones')->count());
            $this->line('Contactos únicos: ' . DB::table('conversaciones')->whereNotNull('canal_contacto_id')->distinct('canal_contacto_id')->count());
        } else {
            $this->error('❌ DUPLICADOS ENCONTRADOS:');
            foreach ($duplicates as $dup) {
                $this->line("Canal contacto ID: {$dup->canal_contacto_id} - Cantidad: {$dup->count}");
            }
        }
    }
}
