<?php

namespace App\Services;

use App\Models\Cuenta;
use App\Models\Perfil;
use App\Models\Venta;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class EntregaMensajeService
{
    public function mensajeEntregaDesdePerfil(Perfil $perfil, $fechaLimite = null, bool $incluirBot = true, bool $incluirAdvertencia = true): string
    {
        $perfil->loadMissing('cuenta.valor.servicio');

        return $this->mensajeEntregaCuenta(
            $perfil->cuenta,
            (int) $perfil->numeroper,
            $perfil->pinper,
            $fechaLimite,
            $incluirBot,
            $incluirAdvertencia
        );
    }

    public function mensajeEntregaCuenta(
        Cuenta $cuenta,
        ?int $numeroPerfil = null,
        ?string $pinPerfil = null,
        $fechaLimite = null,
        bool $incluirBot = true,
        bool $incluirAdvertencia = true
    ): string {
        $cuenta->loadMissing('valor.servicio');

        $idServicio = strtoupper((string) ($cuenta->valor->idser ?? 'SERVICIO'));

        if ($idServicio === 'SPOTIFY') {
            return $this->mensajeSpotify($cuenta, $numeroPerfil, $pinPerfil, $fechaLimite, $incluirAdvertencia);
        }

        if ($idServicio === 'MAGIS' || $idServicio === 'FLUJO') {
            return $this->mensajeFlujo($cuenta, $fechaLimite, $incluirAdvertencia);
        }

        return $this->mensajeGeneral($cuenta, $numeroPerfil, $pinPerfil, $fechaLimite, $incluirBot, $incluirAdvertencia);
    }

    public function mensajeEntregaVenta(Venta $venta): string
    {
        $venta->loadMissing('detalles_venta.perfil.cuenta.valor.servicio');

        $grupos = $this->agruparDetallesPorCuenta($venta->detalles_venta);
        $bloques = [];
        $advertencias = [];

        foreach ($grupos as $idcue => $detallesGrupo) {
            if (empty($detallesGrupo)) {
                continue;
            }

            $primerDetalle = $detallesGrupo[0];
            if (!$primerDetalle->perfil || !$primerDetalle->perfil->cuenta) {
                continue;
            }

            $cuenta = $primerDetalle->perfil->cuenta;
            $idServicio = strtoupper((string) ($cuenta->valor->idser ?? ''));

            // Spotify: cada perfil es un mensaje separado (credenciales distintas)
            if ($idServicio === 'SPOTIFY') {
                foreach ($detallesGrupo as $detalle) {
                    $bloques[] = $this->mensajeEntregaCuenta(
                        $cuenta,
                        (int) ($detalle->perfil->numeroper ?? 0),
                        $detalle->perfil->pinper,
                        $detalle->fechavendet,
                        false,
                        false
                    );
                }
                $advertencias[] = '*Prohibido:* Modificar perfil o clave.';
            }
            // Flujo/MAGIS: una sola vez las credenciales + cantidad de dispositivos
            elseif ($idServicio === 'MAGIS' || $idServicio === 'FLUJO') {
                $mensajeFlujo = $this->mensajeFlujConDispositivos($cuenta, $detallesGrupo[0]->fechavendet, count($detallesGrupo), false);
                $bloques[] = $mensajeFlujo;
                $advertencias[] = '*Prohibido:* Modificar clave.';
            }
            // Otros servicios (Netflix, Disney, Max, Prime, Paramount, Crunchyroll): agrupar perfiles
            else {
                $bloques[] = $this->mensajeGeneralConPerfiles($cuenta, $detallesGrupo, false);
                $advertencias[] = '*Prohibido:* Modificar perfiles o claves.';
            }
        }

        if (empty($bloques)) {
            return '';
        }

        $mensaje = implode("\n\n", $bloques);

        $totalVenta = (float) $venta->totalpagoven;
        if ($totalVenta <= 0) {
            $totalVenta = (float) $venta->detalles_venta->sum('montodet');
        }

        $mensaje .= "\n\n*Total:* $" . number_format($totalVenta, 2, ',', '.');

        $advertencias = array_values(array_unique($advertencias));
        if (count($advertencias) === 1) {
            $mensaje .= "\n" . $advertencias[0];
        } else {
            $mensaje .= "\n*Prohibido:* Modificar credenciales de acceso.";
        }

        return $mensaje;
    }

    public function mensajeRenovacionVenta(Venta $venta): string
    {
        $venta->loadMissing('detalles_venta.perfil.cuenta.valor.servicio');

        $lineas = [
            '*RENOVACION DE SERVICIO*',
            '',
        ];

        $grupos = $this->agruparDetallesPorCuenta($venta->detalles_venta);

        foreach ($grupos as $idcue => $detallesGrupo) {
            if (empty($detallesGrupo)) {
                continue;
            }

            $primerDetalle = $detallesGrupo[0];
            if (!$primerDetalle->perfil || !$primerDetalle->perfil->cuenta) {
                continue;
            }

            $cuenta = $primerDetalle->perfil->cuenta;
            $idServicio = strtoupper((string) ($cuenta->valor->idser ?? 'SERVICIO'));

            // Spotify y otros: listar servicio y fecha
            if ($idServicio === 'SPOTIFY') {
                foreach ($detallesGrupo as $detalle) {
                    $lineas[] = '*' . $idServicio . '*';
                    $fecha = $this->formatearFecha($detalle->fechavendet);
                    if ($fecha !== null) {
                        $lineas[] = 'Fecha limite: ' . $fecha;
                    }
                    $lineas[] = '';
                }
            } else {
                $lineas[] = '*' . $idServicio . '*';
                $fecha = $this->formatearFecha($detallesGrupo[0]->fechavendet);
                if ($fecha !== null) {
                    $lineas[] = 'Fecha limite: ' . $fecha;
                }
                // Mostrar PINs de perfiles si hay múltiples y no es Flujo/MAGIS
                if (count($detallesGrupo) > 1 && $idServicio !== 'MAGIS' && $idServicio !== 'FLUJO') {
                    foreach ($detallesGrupo as $detalle) {
                        if ($detalle->perfil && $detalle->perfil->numeroper) {
                            $pin = (string) $detalle->perfil->pinper;
                            $lineas[] = 'PIN de perfil Nro ' . $detalle->perfil->numeroper . ': ' . $pin;
                        }
                    }
                }
                $lineas[] = '';
            }
        }

        if (count($lineas) === 2) {
            return '';
        }

        if (end($lineas) === '') {
            array_pop($lineas);
        }

        $totalVenta = (float) $venta->totalpagoven;
        if ($totalVenta <= 0) {
            $totalVenta = (float) $venta->detalles_venta->sum('montodet');
        }

        $lineas[] = '';
        $lineas[] = '*Total pagado:* $' . number_format($totalVenta, 2, ',', '.');

        return implode("\n", $lineas);
    }

    private function mensajeSpotify(Cuenta $cuenta, ?int $numeroPerfil, ?string $pinPerfil, $fechaLimite, bool $incluirAdvertencia): string
    {
        [$usuarioSpotify, $claveSpotify] = $this->credencialesSpotify($cuenta, $numeroPerfil, $pinPerfil);

        $partes = [
            '🎵 *SPOTIFY PREMIUM* 🎵',
            '',
            '👤 *Usuario:* ' . $usuarioSpotify,
            '🔑 *Clave:* ' . $claveSpotify,
        ];

        $fecha = $this->formatearFecha($fechaLimite);
        if ($fecha !== null) {
            $partes[] = '*Fecha limite:* ' . $fecha;
        }

        if ($incluirAdvertencia) {
            $partes[] = '';
            $partes[] = '*Prohibido:* Modificar perfil o clave.';
            $partes[] = '¡Gracias por tu confianza! 🎶';
        }

        return implode("\n", $partes);
    }

    private function agruparDetallesPorCuenta($detalles)
    {
        $grupos = [];

        foreach ($detalles as $detalle) {
            if (!$detalle->perfil || !$detalle->perfil->cuenta) {
                continue;
            }

            $idcue = $detalle->perfil->cuenta->idcue;

            if (!isset($grupos[$idcue])) {
                $grupos[$idcue] = [];
            }

            $grupos[$idcue][] = $detalle;
        }

        return $grupos;
    }

    private function mensajeFlujConDispositivos(Cuenta $cuenta, $fechaLimite, int $cantidadDispositivos, bool $incluirAdvertencia): string
    {
        $partes = [
            '*FLUJO*',
            'Usuario: ' . (string) $cuenta->usuariocue,
            'Clave: ' . (string) $cuenta->contrasenacue,
        ];

        $fecha = $this->formatearFecha($fechaLimite);
        if ($fecha !== null) {
            $partes[] = 'Fecha limite: ' . $fecha;
        }

        if ($cantidadDispositivos > 0) {
            $dispositivoTexto = $cantidadDispositivos === 1 ? 'dispositivo' : 'dispositivos';
            $partes[] = 'Dispositivos permitidos: ' . $cantidadDispositivos;
        }

        if ($incluirAdvertencia) {
            $partes[] = '*Prohibido:* Modificar clave.';
        }

        return implode("\n", $partes);
    }

    private function mensajeGeneralConPerfiles(Cuenta $cuenta, array $detallesGrupo, bool $incluirAdvertencia): string
    {
        $servicio = strtoupper((string) ($cuenta->valor->idser ?? 'SERVICIO'));

        $partes = [
            '*' . $servicio . '*',
            'Usuario: ' . (string) $cuenta->usuariocue,
            'Clave: ' . (string) $cuenta->contrasenacue,
        ];

        // Agregar PIN de cada perfil
        foreach ($detallesGrupo as $detalle) {
            if ($detalle->perfil && $detalle->perfil->numeroper) {
                $pin = (string) $detalle->perfil->pinper;
                $partes[] = 'PIN de perfil Nro ' . $detalle->perfil->numeroper . ': ' . $pin;
            }
        }

        // Usar la fecha del primer detalle
        $fecha = $this->formatearFecha($detallesGrupo[0]->fechavendet);
        if ($fecha !== null) {
            $partes[] = 'Fecha limite: ' . $fecha;
        }

        if ($incluirAdvertencia) {
            $partes[] = '*Prohibido:* Modificar perfiles o claves.';
        }

        $bot = (string) ($cuenta->valor->bot ?? '');
        if (trim($bot) !== '') {
            $partes[] = '';
            $partes[] = '*Nota importante:*';
            $partes[] = 'Te dare acceso al bot de codigos. Si se solicita un codigo de acceso (Hogar), puedes obtenerlo aqui:';
            $partes[] = $bot;
        }

        return implode("\n", $partes);
    }

    private function mensajeFlujo(Cuenta $cuenta, $fechaLimite, bool $incluirAdvertencia): string
    {
        $partes = [
            '*FLUJO*',
            'Usuario: ' . (string) $cuenta->usuariocue,
            'Clave: ' . (string) $cuenta->contrasenacue,
        ];

        $fecha = $this->formatearFecha($fechaLimite);
        if ($fecha !== null) {
            $partes[] = 'Fecha limite: ' . $fecha;
        }

        if ($incluirAdvertencia) {
            $partes[] = '*Prohibido:* Modificar clave.';
        }

        return implode("\n", $partes);
    }

    private function mensajeGeneral(
        Cuenta $cuenta,
        ?int $numeroPerfil,
        ?string $pinPerfil,
        $fechaLimite,
        bool $incluirBot,
        bool $incluirAdvertencia
    ): string {
        $servicio = strtoupper((string) ($cuenta->valor->idser ?? 'SERVICIO'));

        $partes = [
            '*' . $servicio . '*',
            'Usuario: ' . (string) $cuenta->usuariocue,
            'Clave: ' . (string) $cuenta->contrasenacue,
        ];

        if ($numeroPerfil !== null) {
            $partes[] = 'PIN de perfil Nro ' . $numeroPerfil . ': ' . (string) $pinPerfil;
        }

        $fecha = $this->formatearFecha($fechaLimite);
        if ($fecha !== null) {
            $partes[] = 'Fecha limite: ' . $fecha;
        }

        if ($incluirAdvertencia) {
            $partes[] = '*Prohibido:* Modificar perfiles o claves.';
        }

        $bot = (string) ($cuenta->valor->bot ?? '');
        if ($incluirBot && trim($bot) !== '') {
            $partes[] = '';
            $partes[] = '*Nota importante:*';
            $partes[] = 'Te dare acceso al bot de codigos. Si se solicita un codigo de acceso (Hogar), puedes obtenerlo aqui:';
            $partes[] = $bot;
        }

        return implode("\n", $partes);
    }

    private function credencialesSpotify(Cuenta $cuenta, ?int $numeroPerfil, ?string $pinPerfil): array
    {
        if ((int) $numeroPerfil === 1) {
            return [
                (string) $cuenta->usuariocue,
                (string) $cuenta->contrasenacue,
            ];
        }

        $pin = trim((string) $pinPerfil);
        if ($pin === '') {
            return [
                (string) $cuenta->usuariocue,
                (string) $cuenta->contrasenacue,
            ];
        }

        if (str_contains($pin, '|')) {
            $partes = explode('|', $pin, 2);
            return [
                trim((string) ($partes[0] ?? $cuenta->usuariocue)),
                trim((string) ($partes[1] ?? $cuenta->contrasenacue)),
            ];
        }

        return [$pin, $pin];
    }

    private function formatearFecha($fecha): ?string
    {
        if ($fecha === null || $fecha === '') {
            return null;
        }

        if ($fecha instanceof CarbonInterface) {
            return $fecha->format('d/m/Y');
        }

        try {
            return Carbon::parse($fecha)->format('d/m/Y');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
