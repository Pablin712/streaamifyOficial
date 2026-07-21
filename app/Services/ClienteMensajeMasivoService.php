<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\ViewClientesUsuarios;
use App\Models\ViewUsuarioActivo;
use Illuminate\Support\Collection;

class ClienteMensajeMasivoService
{
    public function __construct(private MessageVariationService $variador)
    {
    }

    /**
     * Varias versiones completas del mismo mensaje -- se elige una al azar
     * por cliente en buildWebhookPayload() para que el envío masivo no mande
     * texto idéntico a todos los clientes activos del segmento (misma razón
     * que las variantes del cobro diario en TareasBoard).
     */
    public function getDefaultWebsiteInvitationMessageVariants(): array
    {
        $siteUrl = (string) config('services.streamify.public_site_url', 'https://streamify.aaronsoft.es/public/');

        return [
            implode("\n", [
                'Hola 👋',
                'Ya puedes usar nuestro sitio web para gestionar tus servicios de forma más rápida:',
                $siteUrl,
                '',
                'Si te registras con tu mismo teléfono podrás:',
                '• ver tu actividad y servicios',
                '• solicitar soporte técnico',
                '• comprar y renovar por tu cuenta',
                '• recibir ayuda automatizada con IA 🤖',
                '',
                'Es una forma más cómoda y rápida de atenderte mejor.',
            ]),
            implode("\n", [
                '¡Hola! 😊',
                'Te contamos que ya podés gestionar tus servicios desde nuestro sitio web:',
                $siteUrl,
                '',
                'Registrándote con tu mismo número vas a poder:',
                '• revisar tu actividad y servicios activos',
                '• pedir soporte técnico',
                '• comprar y renovar vos mismo, cuando quieras',
                '• recibir ayuda automática con IA 🤖',
                '',
                'Una forma más rápida y cómoda de atenderte.',
            ]),
            implode("\n", [
                'Hola 👋 Novedad importante:',
                'Tenemos un sitio web para que manejes tus servicios sin tener que escribirnos siempre:',
                $siteUrl,
                '',
                'Ahí podés:',
                '• ver el estado de tus servicios',
                '• solicitar soporte técnico',
                '• renovar y comprar directo',
                '• chatear con nuestra IA para ayuda inmediata 🤖',
                '',
                'Registrate con tu número y probalo cuando quieras.',
            ]),
        ];
    }

    public function getDefaultWebsiteUsageBoostMessageVariants(): array
    {
        $siteUrl = (string) config('services.streamify.public_site_url', 'https://streamify.aaronsoft.es/public/');

        return [
            implode("\n", [
                'Hola 👋',
                'Recuerda que ya tienes disponible nuestro sitio web para gestionar tus servicios más rápido:',
                $siteUrl,
                '',
                'Desde ahí puedes:',
                '• mensajearnos directo',
                '• solicitar soporte técnico',
                '• renovar y pagar en línea',
                '• recibir ayuda automatizada con IA 🤖',
                '',
                'Úsalo cuando quieras. Todo ahí es más rápido y automático.',
            ]),
            implode("\n", [
                '¡Hola! 🙌',
                'Un recordatorio: ya tenés nuestro sitio web disponible para tus servicios:',
                $siteUrl,
                '',
                'Ahí podés:',
                '• escribirnos directo',
                '• pedir soporte técnico',
                '• renovar y pagar en línea',
                '• hablar con nuestra IA para ayuda al instante 🤖',
                '',
                'Está siempre disponible, úsalo cuando lo necesites.',
            ]),
            implode("\n", [
                'Hola 👋',
                'No te olvides que tenés nuestro sitio web para manejar tus servicios más rápido:',
                $siteUrl,
                '',
                'Desde ahí podés:',
                '• contactarnos directo',
                '• solicitar soporte técnico',
                '• renovar y pagar online',
                '• recibir ayuda automática con IA 🤖',
                '',
                'Todo más rápido y sin esperar.',
            ]),
        ];
    }

    public function getDefaultWebsiteInvitationMessage(): string
    {
        return $this->getDefaultWebsiteInvitationMessageVariants()[0];
    }

    public function getDefaultWebsiteUsageBoostMessage(): string
    {
        return $this->getDefaultWebsiteUsageBoostMessageVariants()[0];
    }

    public function getMassMessageSegments(): array
    {
        $clientesActivos = $this->getActiveClientsBase();

        $invitationVariants = $this->getDefaultWebsiteInvitationMessageVariants();
        $usageBoostVariants = $this->getDefaultWebsiteUsageBoostMessageVariants();

        return [
            'sin_web' => [
                'label' => 'Clientes activos sin sitio web',
                'message_type' => 'website_invitation',
                'event' => 'clientes.website_invitation',
                'template' => $invitationVariants[0],
                'template_variants' => $invitationVariants,
                'clientes' => $clientesActivos->where('usa_sitio_web', false)->values(),
            ],
            'con_web' => [
                'label' => 'Clientes activos con sitio web',
                'message_type' => 'website_usage_boost',
                'event' => 'clientes.website_usage_boost',
                'template' => $usageBoostVariants[0],
                'template_variants' => $usageBoostVariants,
                'clientes' => $clientesActivos->where('usa_sitio_web', true)->values(),
            ],
        ];
    }

    public function getSegmentSummary(): array
    {
        $segments = $this->getMassMessageSegments();

        return collect($segments)->map(function (array $segment, string $key) {
            return [
                'key' => $key,
                'label' => $segment['label'],
                'count' => $segment['clientes']->count(),
                'template' => $segment['template'],
            ];
        })->all();
    }

    private function getActiveClientsBase(): Collection
    {
        $idsFromUsuariosActivos = ViewUsuarioActivo::query()
            ->whereNotNull('idcli')
            ->pluck('idcli');

        $idsFromClientesUsuarios = ViewClientesUsuarios::query()
            ->where('usuarios', '>', 0)
            ->whereNotNull('idcli')
            ->pluck('idcli');

        $activeIds = $idsFromUsuariosActivos
            ->merge($idsFromClientesUsuarios)
            ->filter()
            ->unique()
            ->values();

        if ($activeIds->isEmpty()) {
            return collect();
        }

        $usuariosPorCliente = ViewUsuarioActivo::query()
            ->selectRaw('idcli, COUNT(*) as total_usuarios_activos')
            ->whereIn('idcli', $activeIds)
            ->groupBy('idcli')
            ->pluck('total_usuarios_activos', 'idcli');

        $clientesUsuarios = ViewClientesUsuarios::query()
            ->whereIn('idcli', $activeIds)
            ->get()
            ->keyBy('idcli');

        return Cliente::query()
            ->whereIn('idcli', $activeIds)
            ->whereNotNull('telefonocli')
            ->where('telefonocli', '!=', '')
            ->orderBy('nombrecli')
            ->get()
            ->map(function (Cliente $cliente) use ($usuariosPorCliente, $clientesUsuarios) {
                $telefono = (string) $cliente->telefonocli;
                $telefonoNormalizado = preg_replace('/\D+/', '', $telefono);
                $resumenCliente = $clientesUsuarios->get($cliente->idcli);
                $usaSitioWeb = !empty($cliente->email) && !empty($cliente->password);

                return [
                    'idcli' => $cliente->idcli,
                    'nombre' => $cliente->nombrecli,
                    'telefono' => $telefono,
                    'telefono_normalizado' => $telefonoNormalizado ?: null,
                    'email' => $cliente->email,
                    'usuarios_activos' => (int) ($usuariosPorCliente[$cliente->idcli] ?? ($resumenCliente->usuarios ?? 0)),
                    'autenticado' => $usaSitioWeb,
                    'usa_sitio_web' => $usaSitioWeb,
                    'saldo' => $cliente->saldo,
                ];
            })
            ->filter(function (array $cliente) {
                return !empty($cliente['telefono_normalizado']);
            })
            ->values();
    }

    /**
     * Arma el payload para el webhook n8n "/masivo" -- el mismo que consume
     * TareaController::enviarCobrosWspMasivo() para el cobro diario, así que
     * usa la misma forma: "destinatarios" (array de {telefono, mensaje} por
     * persona), no un "mensaje" único compartido. Antes este método mandaba
     * {mensaje, clientes} -- una forma que ese flujo de n8n no sabe leer (solo
     * lee body.destinatarios), así que además de variar el texto esto corrige
     * que el mensaje masivo a clientes activos llegue bien formado.
     */
    public function buildWebhookPayload(string $segmentKey, string $mensaje, $empleado): array
    {
        $segments = $this->getMassMessageSegments();
        $segment = $segments[$segmentKey] ?? null;

        if (!$segment) {
            return [
                'event' => 'clientes.mass_message',
                'trace_id' => 'clientes-masivo-' . now()->format('Ymd-His'),
                'message_type' => 'unknown',
                'destinatarios' => [],
                'clientes_count' => 0,
                'segment' => $segmentKey,
                'meta' => [
                    'site_url' => config('services.streamify.public_site_url', 'https://streamify.aaronsoft.es/public/'),
                ],
                'empleado' => [
                    'idemp' => $empleado->idemp,
                    'nombreemp' => $empleado->nombreemp,
                    'usuarioemp' => $empleado->usuarioemp,
                ],
                'sent_at' => now()->toIso8601String(),
            ];
        }

        $clientes = $segment['clientes'];
        $mensaje = trim($mensaje);
        $esPlantillaSugerida = $mensaje === trim($segment['template']);

        $destinatarios = $clientes->map(function (array $cliente) use ($segment, $mensaje, $esPlantillaSugerida) {
            $textoFinal = $esPlantillaSugerida
                ? $this->variador->pickVariant($segment['template_variants'])
                : $this->variador->lightlyVary($mensaje);

            return [
                'nombre' => $cliente['nombre'],
                'telefono' => $cliente['telefono_normalizado'] ?: $cliente['telefono'],
                'idcli' => $cliente['idcli'],
                'mensaje' => $textoFinal,
            ];
        })->values()->all();

        return [
            'event' => $segment['event'],
            'trace_id' => 'clientes-masivo-' . $segmentKey . '-' . now()->format('Ymd-His'),
            'message_type' => $segment['message_type'],
            'destinatarios' => $destinatarios,
            'clientes_count' => $clientes->count(),
            'segment' => $segmentKey,
            'meta' => [
                'site_url' => config('services.streamify.public_site_url', 'https://streamify.aaronsoft.es/public/'),
                'segment_label' => $segment['label'],
            ],
            'empleado' => [
                'idemp' => $empleado->idemp,
                'nombreemp' => $empleado->nombreemp,
                'usuarioemp' => $empleado->usuarioemp,
            ],
            'sent_at' => now()->toIso8601String(),
        ];
    }
}
