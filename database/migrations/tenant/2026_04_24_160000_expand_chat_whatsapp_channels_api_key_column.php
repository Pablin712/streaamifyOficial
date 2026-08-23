<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE chat_whatsapp_channels MODIFY api_key TEXT NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE chat_whatsapp_channels MODIFY api_key VARCHAR(191) NOT NULL');
    }
};
