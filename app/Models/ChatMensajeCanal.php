    protected $fillable = [
        'idmsg',
        'idconv',
        'contacto_canal_id',
        'canal',
        'direccion',
        'external_message_id',
        'external_thread_id',
        'external_status',
        'media_id',
        'media_url',
        'media_mime_type',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function mensaje()
    {
        return $this->belongsTo(Mensaje::class, 'idmsg', 'idmsg');
    }

    public function conversacion()
    {
        return $this->belongsTo(Conversacion::class, 'idconv', 'idconv');
    }

    public function contacto()
    {
        return $this->belongsTo(ChatContactoCanal::class, 'contacto_canal_id');
    }

    // Métodos de acceso para los datos en payload
    public function getMensajeAttribute()
    {
        return $this->payload['mensaje_texto'] ?? $this->payload['mensaje'] ?? null;
    }

    public function getTipoContenidoAttribute()
    {
        return $this->payload['tipo_contenido'] ?? $this->payload['tipo'] ?? 'texto';
    }

    public function getLeidoAttribute()
    {
        return $this->payload['leido'] ?? false;
    }

    public function setLeidoAttribute($value)
    {
        $payload = $this->payload ?? [];
        $payload['leido'] = (bool) $value;
        $this->payload = $payload;
    }
}
