<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonnaKnowledgeBase extends Model
{
    protected $table = 'donna_knowledge_bases';

    protected $fillable = ['client_id', 'service_id', 'name', 'description', 'status'];

    public function items()
    {
        return $this->hasMany(DonnaKnowledgeItem::class, 'knowledge_base_id');
    }

    public function activeItems()
    {
        return $this->items()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
