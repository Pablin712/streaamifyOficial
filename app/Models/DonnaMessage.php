<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonnaMessage extends Model
{
    protected $table = 'donna_messages';

    protected $fillable = [
        'client_id', 'service_id', 'channel_id', 'conversation_id',
        'direction', 'sender_type', 'message_type',
        'provider_message_id', 'external_chat_id', 'sender_identifier', 'sender_name',
        'content_text', 'transcription_text', 'ocr_text', 'ai_response_text',
        'media_url', 'media_mime_type', 'media_size',
        'processing_status', 'blocked_reason',
        'raw_payload_json', 'metadata_json',
    ];

    protected $casts = [
        'raw_payload_json' => 'array',
        'metadata_json'    => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(DonnaConversation::class, 'conversation_id');
    }

    public function channel()
    {
        return $this->belongsTo(DonnaChannel::class, 'channel_id');
    }

    public function getEffectiveTextAttribute(): ?string
    {
        return $this->transcription_text ?? $this->ocr_text ?? $this->content_text;
    }

    public function hasMedia(): bool
    {
        return in_array($this->message_type, ['audio', 'image', 'video', 'document']);
    }
}
