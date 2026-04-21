<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chat_settings')) {
            Schema::create('chat_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('type', 20)->default('string');
                $table->timestamps();
            });
        }

        $defaults = [
            'chat_allow_text' => ['1', 'bool'],
            'chat_allow_image' => ['1', 'bool'],
            'chat_allow_audio' => ['1', 'bool'],
            'chat_allow_document' => ['0', 'bool'],
            'chat_allow_location' => ['0', 'bool'],
            'chat_allow_template' => ['0', 'bool'],
            'chat_realtime_enabled' => ['1', 'bool'],
            'chat_auto_assign' => ['0', 'bool'],
            'chat_max_upload_mb' => ['10', 'int'],
        ];

        foreach ($defaults as $key => [$value, $type]) {
            DB::table('chat_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => $type,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_settings');
    }
};

