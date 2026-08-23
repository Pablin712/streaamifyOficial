<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mensajes', function (Blueprint $table) {
            $table->longText('mensaje_original')->nullable()->after('contenido');
            $table->longText('texto_extraido')->nullable()->after('mensaje_original');
            $table->longText('texto_agente')->nullable()->after('texto_extraido');
            $table->string('media_kind', 40)->nullable()->after('tipo_contenido');
            $table->string('media_file_name', 255)->nullable()->after('mime_type');
            $table->longText('media_transcription')->nullable()->after('media_file_name');
            $table->longText('media_caption')->nullable()->after('media_transcription');
            $table->json('media_analysis_json')->nullable()->after('media_caption');
        });
    }

    public function down(): void
    {
        Schema::table('mensajes', function (Blueprint $table) {
            $table->dropColumn([
                'mensaje_original',
                'texto_extraido',
                'texto_agente',
                'media_kind',
                'media_file_name',
                'media_transcription',
                'media_caption',
                'media_analysis_json',
            ]);
        });
    }
};
