<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE mensajes MODIFY COLUMN tipo_contenido VARCHAR(20) NOT NULL DEFAULT 'texto'");
        DB::statement("ALTER TABLE mensajes MODIFY COLUMN tipo_contenido ENUM('texto', 'imagen', 'archivo', 'audio', 'sistema', 'video', 'documento') NOT NULL DEFAULT 'texto'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE mensajes MODIFY COLUMN tipo_contenido VARCHAR(20) NOT NULL DEFAULT 'texto'");
        DB::statement("ALTER TABLE mensajes MODIFY COLUMN tipo_contenido ENUM('texto', 'imagen', 'archivo', 'audio', 'sistema') NOT NULL DEFAULT 'texto'");
    }
};
