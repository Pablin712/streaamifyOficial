<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonnaKnowledgeItem extends Model
{
    protected $table = 'donna_knowledge_items';

    protected $fillable = [
        'knowledge_base_id', 'client_id', 'service_id',
        'title', 'type', 'content_text', 'source_url',
        'metadata_json', 'is_active',
        'embedding_json', 'embedding_model', 'embedding_updated_at',
    ];

    protected $casts = [
        'metadata_json'         => 'array',
        'is_active'              => 'boolean',
        'embedding_json'         => 'array',
        'embedding_updated_at'   => 'datetime',
    ];

    /**
     * Texto que se vectoriza para la búsqueda semántica.
     * Se incluye el title porque suele tener la señal más fuerte (nombre del
     * producto/servicio) y el type para ayudar a discriminar variantes.
     */
    public function embeddingSourceText(): string
    {
        return trim(($this->title ? $this->title . ". " : '') . ($this->content_text ?? ''));
    }

    public function needsEmbedding(): bool
    {
        return empty($this->embedding_json) || $this->embedding_updated_at === null
            || $this->updated_at?->greaterThan($this->embedding_updated_at);
    }

    public function knowledgeBase()
    {
        return $this->belongsTo(DonnaKnowledgeBase::class, 'knowledge_base_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
