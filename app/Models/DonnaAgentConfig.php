<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonnaAgentConfig extends Model
{
    protected $table = 'donna_agent_configs';

    protected $fillable = [
        'client_id', 'subscription_id', 'service_type',
        'agent_name', 'business_name', 'business_description',
        'personal_context', 'business_context', 'business_logic',
        'main_prompt', 'fallback_prompt', 'welcome_message',
        'out_of_hours_message', 'tone', 'language', 'timezone',
        'working_hours_json', 'human_handoff_msg',
        'spreadsheet_id', 'spreadsheet_name', 'calendar_id',
        'is_active',
    ];

    protected $casts = [
        'working_hours_json' => 'array',
        'is_active'          => 'boolean',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'client_id', 'idcli');
    }

    public function subscription()
    {
        return $this->belongsTo(DonnaSubscription::class, 'subscription_id');
    }
}
