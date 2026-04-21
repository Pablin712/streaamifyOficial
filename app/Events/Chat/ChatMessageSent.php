<?php

namespace App\Events\Chat;

use App\Models\Conversacion;
use App\Models\Mensaje;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Conversacion $conversation,
        public readonly Mensaje $message
    ) {
    }
}

