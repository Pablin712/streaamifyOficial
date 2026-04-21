<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'chat_webhook_token' => ['', 'string'],
            'n8n_webhook_url' => ['', 'string'],
            'evoapi_base_url' => ['', 'string'],
            'evoapi_api_key' => ['', 'string'],
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
        DB::table('chat_settings')->whereIn('key', [
            'chat_webhook_token',
            'n8n_webhook_url',
            'evoapi_base_url',
            'evoapi_api_key',
        ])->delete();
    }
};
