<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('empleados_online', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idemp');
            $table->timestamp('ultima_actividad')->useCurrent();
            $table->string('session_id')->unique(); // WebSocket session
            $table->timestamps();

            $table->foreign('idemp')
                  ->references('idemp')
                  ->on('empleados')
                  ->onDelete('cascade');

            $table->index('ultima_actividad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados_online');
    }
};
