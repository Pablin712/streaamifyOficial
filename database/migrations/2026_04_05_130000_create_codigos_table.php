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
        Schema::create('codigos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 6)->nullable();
            $table->text('mensaje')->nullable();
            $table->string('telefono', 15)->nullable();
            $table->enum('estado', ['esperando', 'enviado'])->default('esperando');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codigos');
    }
};
