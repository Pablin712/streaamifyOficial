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
        // MySQL/MariaDB no permite modificar ENUM directamente con ALTER
        // Necesitamos cambiar temporalmente a VARCHAR, luego a ENUM con el nuevo valor
        DB::statement("ALTER TABLE mensajes MODIFY COLUMN tipo_remitente VARCHAR(20) NOT NULL");
        DB::statement("ALTER TABLE mensajes MODIFY COLUMN tipo_remitente ENUM('cliente', 'empleado', 'sistema', 'ia', 'bot') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mensajes MODIFY COLUMN tipo_remitente VARCHAR(20) NOT NULL");
        DB::statement("ALTER TABLE mensajes MODIFY COLUMN tipo_remitente ENUM('cliente', 'empleado', 'sistema', 'ia') NOT NULL");
    }
};
