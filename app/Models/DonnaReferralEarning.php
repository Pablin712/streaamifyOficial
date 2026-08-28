<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonnaReferralEarning extends Model
{
    protected $table = 'donna_referral_earnings';

    protected $fillable = [
        'referral_partner_id',
        'subscription_id',
        'client_id',
        'event_type',
        'payment_amount',
        'commission_amount',
    ];

    protected $casts = [
        'payment_amount'    => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    public function partner()
    {
        return $this->belongsTo(DonnaReferralPartner::class, 'referral_partner_id');
    }

    public function subscription()
    {
        return $this->belongsTo(DonnaSubscription::class, 'subscription_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'client_id', 'idcli');
    }

    public function getEventTypeLabelAttribute(): string
    {
        return match ($this->event_type) {
            'activation' => 'Activación',
            'renewal'    => 'Renovación',
            default      => $this->event_type,
        };
    }
}
