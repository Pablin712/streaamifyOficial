<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\ViewClientesUsuarios;
use App\Models\ViewUsuarioActivo;
use Illuminate\Support\Collection;

class ClienteMensajeMasivoService
{
    public function getDefaultWebsiteInvitationMessage(): string
    {
        $siteUrl = (string) config('services.streamify.public_site_url', 'https://streamify.aaronsoft.es/public/');

        return implode("\n", [
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
        ]);
    }

    public function getDefaultWebsiteUsageBoostMessage(): string
    {
        $siteUrl = (string) config('services.streamify.public_site_url', 'https://streamify.aaronsoft.es/public/');

        return implode("\n", [
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
        ]);
    }

    public function getMassMessageSegments(): array
    {
        $clientesActivos = $this->getActiveClientsBase();

        return [
            'sin_web' => [
                'label' => 'Clientes activos sin sitio web',
                'message_type' => 'website_invitation',
                'event' => 'clientes.website_invitation',
                'template' => $this->getDefaultWebsiteInvitationMessage(),
                'clientes' => $clientesActivos->where('usa_sitio_web', false)->values(),
            ],
            'con_web' => [
                'label' => 'Clientes activos con sitio web',
                'message_type' => 'website_usage_boost',
                'event' => 'clientes.website_usage_boost',
                'template' => $this->getDefaultWebsiteUsageBoostMessage(),
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

    public function buildWebhookPayload(string $segmentKey, string $mensaje, $empleado): array
    {
        $segments = $this->getMassMessageSegments();
        $segment = $segments[$segmentKey] ?? null;

        if (!$segment) {
            return [
                'event' => 'clientes.mass_message',
                'trace_id' => 'clientes-masivo-' . now()->format('Ymd-His'),
                'message_type' => 'unknown',
                'mensaje' => trim($mensaje),
                'clientes_count' => 0,
                'clientes' => collect(),
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

        return [
            'event' => $segment['event'],
            'trace_id' => 'clientes-masivo-' . $segmentKey . '-' . now()->format('Ymd-His'),
            'message_type' => $segment['message_type'],
            'mensaje' => trim($mensaje),
            'clientes_count' => $clientes->count(),
            'clientes' => $clientes,
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
