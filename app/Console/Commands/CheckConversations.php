<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckConversations extends Command
{
    protected $signature = 'check:conversations';
    protected $description = 'Check all conversations';

    public function handle()
    {
        $total = DB::table('conversaciones')->count();
        $this->line("Total conversaciones: $total");

        if ($total > 0) {
            $conversations = DB::table('conversaciones')
                ->select('idconv', 'idcli', 'canal_contacto_id', 'canal_principal', 'estado')
                ->limit(10)
                ->get();

            $this->table(
                ['ID', 'Cliente', 'Contacto ID', 'Canal', 'Estado'],
                $conversations->map(fn($c) => [$c->idconv, $c->idcli, $c->canal_contacto_id, $c->canal_principal, $c->estado])
            );
        } else {
            $this->warn('La tabla conversaciones está vacía');
        }

        // Verificar tabla chat_contactos_canal
        $totalContacts = DB::table('chat_contactos_canal')->count();
        $this->line("\nTotal contactos canal: $totalContacts");

        // Verificar mensajes
        $totalMessages = DB::table('mensajes')->count();
        $this->line("Total mensajes: $totalMessages");

        $totalChatMessages = DB::table('chat_mensajes_canal')->count();
        $this->line("Total chat_mensajes_canal: $totalChatMessages");
    }
}
