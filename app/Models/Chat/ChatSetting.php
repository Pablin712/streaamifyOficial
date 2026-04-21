<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class ChatSetting extends Model
{
    protected $table = 'chat_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
    ];
}

