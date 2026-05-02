<?php

namespace Database\Seeders;

use App\Models\ChatMemoriaNegocio;
use Illuminate\Database\Seeder;

class ChatSoportePlaybooksSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'tipo' => 'guion',
                'clave' => 'soporte_estilo_operativo',
                'titulo' => 'Estilo operativo soporte',
                'contenido' => 'Resolver primero con pasos cortos y claros. Si no se resuelve o hay bloqueo estructural, crear soporte y escalar. No explicar problemas internos al cliente.',
                'resumen' => 'Soporte breve, resolutivo y sin exponer detalles internos.',
                'visibilidad' => 'ambas',
                'tags' => ['soporte', 'estilo', 'resolucion_rapida'],
                'fuente' => 'subagente_soporte',
                'prioridad' => 2,
                'activo' => true,
            ],
            [
                'tipo' => 'guion',
                'clave' => 'soporte_arbol_decision_basico',
                'titulo' => 'Arbol de decision soporte',
                'contenido' => 'Paso 1: consultar usuarios activos por telefono. Paso 2: si estado vencido, indicar renovacion. Paso 3: si estado activo o por_vencer, guiar con playbook del servicio. Paso 4: si no resuelve o es caso critico (sin suscripcion, pin incorrecto real, contrasena incorrecta real, cuenta caida, muchos dispositivos), crear soporte.',
                'resumen' => 'Diagnostico por estado y escalamiento solo cuando aplica.',
                'visibilidad' => 'ambas',
                'tags' => ['soporte', 'diagnostico', 'escalamiento'],
                'fuente' => 'subagente_soporte',
                'prioridad' => 3,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'soporte_netflix_pide_codigo',
                'titulo' => 'Netflix pide codigo',
                'contenido' => 'Indicar al cliente que siga los pasos en pantalla y solicite codigo Netflix desde su panel cuando aplique. Si despues de intentarlo no avanza, crear soporte con tipo otro o sin suscripcion segun el error visible.',
                'resumen' => 'Guiar pasos de codigo Netflix y escalar solo si persiste.',
                'visibilidad' => 'cliente',
                'tags' => ['soporte', 'netflix', 'codigo'],
                'fuente' => 'playbook_streaming',
                'prioridad' => 10,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'soporte_tv_inicio_sesion_codigo',
                'titulo' => 'TV pide codigo de vinculacion',
                'contenido' => 'Si la TV pide codigo o vinculacion, pedir que ingrese con el metodo de codigo mostrado en pantalla desde su celular o navegador. Dar instrucciones paso a paso, una accion por mensaje.',
                'resumen' => 'Resolver vinculacion por codigo en TV con pasos cortos.',
                'visibilidad' => 'cliente',
                'tags' => ['soporte', 'tv', 'codigo', 'vinculacion'],
                'fuente' => 'playbook_streaming',
                'prioridad' => 11,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'soporte_disney_inicio_sesion',
                'titulo' => 'Disney inicio de sesion',
                'contenido' => 'Guiar validacion basica: correo correcto, contrasena exacta, seleccion de perfil asignado y cierre de sesiones anteriores si aplica. Si persiste error de acceso, crear soporte.',
                'resumen' => 'Checklist corto de acceso Disney antes de escalar.',
                'visibilidad' => 'cliente',
                'tags' => ['soporte', 'disney', 'login'],
                'fuente' => 'playbook_streaming',
                'prioridad' => 12,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'soporte_max_inicio_sesion',
                'titulo' => 'Max inicio de sesion',
                'contenido' => 'Guiar validacion basica: usuario y clave exactos, perfil correcto, reinicio de app, volver a iniciar sesion. Si aparece error persistente de cuenta o suscripcion, crear soporte.',
                'resumen' => 'Pasos cortos de acceso Max y escalamiento por error persistente.',
                'visibilidad' => 'cliente',
                'tags' => ['soporte', 'max', 'login'],
                'fuente' => 'playbook_streaming',
                'prioridad' => 13,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'soporte_spotify_inicio_sesion',
                'titulo' => 'Spotify acceso',
                'contenido' => 'Guiar ingreso con usuario asignado, verificar que no haya mezcla con cuenta personal y forzar cierre de sesiones si el dispositivo lo permite. Si no permite acceso despues de pasos basicos, crear soporte.',
                'resumen' => 'Resolver conflicto de cuentas en Spotify y escalar si persiste.',
                'visibilidad' => 'cliente',
                'tags' => ['soporte', 'spotify', 'login'],
                'fuente' => 'playbook_streaming',
                'prioridad' => 14,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'soporte_crunchyroll_inicio_sesion',
                'titulo' => 'Crunchyroll acceso',
                'contenido' => 'Guiar ingreso con credenciales compartidas, validar region/dispositivo si hay mensaje de restriccion y recomendar reiniciar app. Si reporta sin suscripcion o bloqueo persistente, crear soporte.',
                'resumen' => 'Pasos basicos Crunchyroll y escalamiento por suscripcion/bloqueo.',
                'visibilidad' => 'cliente',
                'tags' => ['soporte', 'crunchyroll', 'login'],
                'fuente' => 'playbook_streaming',
                'prioridad' => 15,
                'activo' => true,
            ],
            [
                'tipo' => 'guion',
                'clave' => 'soporte_mensaje_cierre_ticket',
                'titulo' => 'Mensaje de cierre cuando se crea soporte',
                'contenido' => 'Cuando se cree soporte, responder: Tu caso ya fue registrado y nuestro equipo lo revisara pronto. Te ayudaremos apenas quede resuelto. Evitar detalles tecnicos internos.',
                'resumen' => 'Confirmar ticket con mensaje calmado y sin detalles internos.',
                'visibilidad' => 'cliente',
                'tags' => ['soporte', 'ticket', 'cierre'],
                'fuente' => 'subagente_soporte',
                'prioridad' => 20,
                'activo' => true,
            ],
        ];

        foreach ($items as $item) {
            ChatMemoriaNegocio::updateOrCreate(
                [
                    'tipo' => $item['tipo'],
                    'clave' => $item['clave'],
                ],
                $item
            );
        }
    }
}
