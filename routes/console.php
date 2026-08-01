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
// --limit=150 acota el costo (~150 llamadas/semana, ~$1.70 a precio actual de
// Claude Sonnet 5). --inactividad-horas=24 evita analizar conversaciones que
// solo estan en una pausa nocturna (no realmente terminadas).
Schedule::command('whatsapp:analizar-satisfaccion --limit=150 --inactividad-horas=24')
    ->timezone(config('app.timezone'))
    ->weeklyOn(1, '03:00');
