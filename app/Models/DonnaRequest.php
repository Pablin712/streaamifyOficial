<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonnaRequest extends Model
{
    protected $table = 'donna_requests';

    protected $fillable = [
        'client_id',
        'plan_id',
        'status',
        'message',
        'employee_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(DonnaPlan::class, 'plan_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'client_id', 'idcli');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(Empleado::class, 'reviewed_by', 'idemp');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'Pendiente',
            'approved' => 'Aprobada',
            'rejected' => 'Rechazada',
            default    => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default    => 'secondary',
        };
    }
}
