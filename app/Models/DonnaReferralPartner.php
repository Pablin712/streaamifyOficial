<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonnaReferralPartner extends Model
{
    protected $table = 'donna_referral_partners';

    protected $fillable = [
        'client_id',
        'code',
        'discount_amount',
        'commission_amount',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'discount_amount'   => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'is_active'         => 'boolean',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'client_id', 'idcli');
    }

    public function earnings()
    {
        return $this->hasMany(DonnaReferralEarning::class, 'referral_partner_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Precio final para el plan dado tras aplicar el descuento, sin bajar de 0.
     */
    public function discountedPrice(DonnaPlan $plan): float
    {
        return max(0, (float) $plan->price - (float) $this->discount_amount);
    }
}
