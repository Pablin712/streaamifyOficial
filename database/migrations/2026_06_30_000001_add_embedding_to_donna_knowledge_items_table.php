<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donna_knowledge_items', function (Blueprint $table) {
            // Vector de embedding (array de floats) guardado como JSON.
            // MySQL 8.4 no tiene tipo VECTOR nativo (llega en 9.x), así que se
            // guarda como JSON y la similitud de coseno se calcula en PHP.
            $table->json('embedding_json')->nullable()->after('metadata_json');
            $table->string('embedding_model', 60)->nullable()->after('embedding_json');
            $table->timestamp('embedding_updated_at')->nullable()->after('embedding_model');

            $table->index(['client_id', 'is_active', 'embedding_updated_at'], 'donna_ki_embed_idx');
        });
    }

    public function down(): void
    {
        Schema::table('donna_knowledge_items', function (Blueprint $table) {
            $table->dropIndex('donna_ki_embed_idx');
            $table->dropColumn(['embedding_json', 'embedding_model', 'embedding_updated_at']);
        });
    }
};
