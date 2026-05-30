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
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'is_active'     => 'boolean',
    ];

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
