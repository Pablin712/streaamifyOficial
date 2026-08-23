<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_agente_imagenes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->text('descripcion');
            $table->string('categoria', 60)->default('general');
            $table->string('archivo', 255);
            $table->string('mime_type', 80)->nullable();
            $table->json('tags')->nullable();
            $table->unsignedSmallInteger('prioridad')->default(50);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_agente_imagenes');
    }
};
