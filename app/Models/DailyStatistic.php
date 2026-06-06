<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class DailyStatistic extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     */
    protected $table = 'daily_statistics';
    public $timestamps = true;
    /**
     * Los atributos que son asignables en masa.
     */
    protected $fillable = [
        'date',
        'active_users',
        'usuarios_a_cobrar',
        'espacios',
        'cliente_mas_facturado',
        'total_customers',
        'affected_customers',
        'pending_payments',
        'danger_accounts',
        'accounts',
        'daily_revenue',
        'daily_cost',
        'daily_bill',
        'daily_sales',
        'daily_tasks',
        'new_customers',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'date' => 'date',
        'daily_revenue' => 'decimal:2',
    ];
}
