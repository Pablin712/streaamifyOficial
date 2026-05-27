<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonnaPlan extends Model
{
    protected $table = 'donna_plans';

    protected $fillable = [
        'code',
        'name',
        'service_type',
        'description',
        'price',
        'currency',
        'billing_cycle',
        'features_json',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features_json' => 'array',
        'is_active'     => 'boolean',
        'price'         => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('service_type');
    }

    public function scopePersonal($query)
    {
        return $query->where('service_type', 'personal');
    }

    public function scopeBusiness($query)
    {
        return $query->where('service_type', 'business');
    }

    public function getBillingCycleLabelAttribute(): string
    {
        return match($this->billing_cycle) {
            'monthly'  => 'mes',
            'yearly'   => 'año',
            'one_time' => 'pago único',
            default    => $this->billing_cycle,
        };
    }
}
