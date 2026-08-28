<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonnaSubscription extends Model
{
    protected $table = 'donna_subscriptions';

    protected $fillable = [
        'client_id',
        'plan_id',
        'service_type',
        'status',
        'billing_cycle',
        'price_paid',
        'currency',
        'starts_at',
        'expires_at',
        'last_payment_at',
        'next_payment_due',
        'is_enabled',
        'suspended_reason',
        'notes',
        'activated_by',
        'referral_partner_id',
        'referral_discount_amount',
        'referral_commission_percent',
    ];

    protected $casts = [
        'starts_at'       => 'datetime',
        'expires_at'      => 'datetime',
        'last_payment_at' => 'datetime',
        'next_payment_due'=> 'date',
        'is_enabled'      => 'boolean',
        'price_paid'      => 'decimal:2',
        'referral_discount_amount'   => 'decimal:2',
        'referral_commission_percent' => 'decimal:2',
    ];

    public function plan()
    {
        return $this->belongsTo(DonnaPlan::class, 'plan_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'client_id', 'idcli');
    }

    public function referralPartner()
    {
        return $this->belongsTo(DonnaReferralPartner::class, 'referral_partner_id');
    }

    public function activatedByEmployee()
    {
        return $this->belongsTo(Empleado::class, 'activated_by', 'idemp');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_enabled', true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->is_enabled
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function daysRemaining(): ?int
    {
        if ($this->expires_at === null) return null;
        $diff = (int) now()->diffInDays($this->expires_at, false);
        return $diff;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Pendiente',
            'active'    => 'Activo',
            'suspended' => 'Suspendido',
            'expired'   => 'Vencido',
            'cancelled' => 'Cancelado',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active'    => 'success',
            'pending'   => 'warning',
            'suspended' => 'secondary',
            'expired'   => 'danger',
            'cancelled' => 'dark',
            default     => 'light',
        };
    }
}
