<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Analisis IA de calidad de conversaciones de WhatsApp (Fase 1 - prototipo).
// Ver docs/optimizacion/idea-soporte.md. Sin --desde: usa el default del
// comando (ultimos 7 dias), calculado en el momento en que corre.
Schedule::command('whatsapp:analizar-satisfaccion --limit=300')
    ->timezone(config('app.timezone'))
    ->weeklyOn(1, '03:00');
