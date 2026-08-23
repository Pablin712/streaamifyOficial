<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE chat_whatsapp_channels MODIFY COLUMN color ENUM('verde', 'azul', 'naranja', 'otro') NOT NULL DEFAULT 'otro'");
    }

    public function down(): void
    {
        DB::statement("UPDATE chat_whatsapp_channels SET color = 'otro' WHERE color = 'naranja'");
        DB::statement("ALTER TABLE chat_whatsapp_channels MODIFY COLUMN color ENUM('verde', 'azul', 'otro') NOT NULL DEFAULT 'otro'");
    }
};
