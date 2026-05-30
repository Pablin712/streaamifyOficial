<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonnaConversation extends Model
{
    protected $table = 'donna_conversations';

    protected $fillable = [
        'client_id', 'service_id', 'channel_id',
        'external_chat_id', 'sender_identifier', 'sender_name',
        'status', 'last_message_at', 'last_message_preview',
        'memory_summary', 'assigned_to_user_id', 'metadata_json',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'metadata_json'   => 'array',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'client_id', 'idcli');
    }

    public function channel()
    {
        return $this->belongsTo(DonnaChannel::class, 'channel_id');
    }

    public function messages()
    {
        return $this->hasMany(DonnaMessage::class, 'conversation_id');
    }

    public function recentMessages(int $limit = 20)
    {
        return $this->messages()
            ->whereIn('sender_type', ['final_customer', 'ai_agent'])
            ->latest()
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(fn ($m) => [
                'role'    => $m->sender_type === 'final_customer' ? 'user' : 'assistant',
                'content' => $m->transcription_text ?? $m->content_text ?? '',
            ])
            ->values()
            ->toArray();
    }

    public function isHumanTakeover(): bool
    {
        return $this->status === 'human_takeover';
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
