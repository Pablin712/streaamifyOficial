<?php

namespace App\Services;

use App\Models\AjusteApariencia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Apariencia global: tema y modo oscuro para TODA la plataforma.
 *
 * Antes esto vivia en localStorage, asi que cada navegador y cada sesion de
 * empleado tenia su propia copia y lo que elegia el administrador no llegaba
 * a los demas. Ahora la fuente de verdad es la base de datos y el navegador
 * solo la refleja: un cambio se ve en todos los dispositivos y sesiones en
 * cuanto recargan.
 *
 * El catalogo de temas vive aqui, en PHP, y se entrega a JavaScript desde el
 * layout. Antes estaba duplicado en theme-manager.js y en la vista de sistema,
 * y se desincronizaba.
 */
class AparienciaService
{
    private const CACHE_KEY = 'apariencia.global';

    /** Valores usados si la tabla todavia no existe (p. ej. durante un deploy). */
    private const RESPALDO = [
        'tema'            => 'default',
        'modo_oscuro'     => false,
        'auto_temporada'  => true,
        'actualizado_por' => null,
    ];

    /**
     * Catalogo de temas.
     *
     * - `auto`: ventana de fechas en la que el tema se activa solo (si el
     *   administrador dejo encendido `auto_temporada`). `null` = permanente.
     * - `decoracion`: clave que consume decorations.js, o null.
     * - `paleta`: colores para la vista previa del selector.
     */
    public static function temas(): array
    {
        return [
            'default' => [
                'nombre'      => 'Streamify Original',
                'icono'       => '🎨',
                'descripcion' => 'Azul, dorado y rojo de marca',
                'decoracion'  => null,
                'auto'        => null,
                'paleta'      => ['#274698', '#E4B100', '#D41216'],
            ],
            'midnight' => [
                'nombre'      => 'Medianoche',
                'icono'       => '🌙',
                'descripcion' => 'Oscuro permanente, sobrio',
                'decoracion'  => null,
                'auto'        => null,
                'paleta'      => ['#8ba3f0', '#16151a', '#f0c435'],
            ],
            'neon' => [
                'nombre'      => 'Neón',
                'icono'       => '💜',
                'descripcion' => 'Violeta y cian eléctrico',
                'decoracion'  => null,
                'auto'        => null,
                'paleta'      => ['#7c3aed', '#06b6d4', '#f472b6'],
            ],
            'oceano' => [
                'nombre'      => 'Océano',
                'icono'       => '🌊',
                'descripcion' => 'Azules profundos y turquesa',
                'decoracion'  => null,
                'auto'        => null,
                'paleta'      => ['#0369a1', '#0d9488', '#7dd3fc'],
            ],
            'christmas' => [
                'nombre'      => 'Navidad',
                'icono'       => '🎄',
                'descripcion' => 'Rojo y verde navideño',
                'decoracion'  => 'christmas',
                'auto'        => ['mesInicio' => 12, 'diaInicio' => 2, 'mesFin' => 12, 'diaFin' => 26],
                'paleta'      => ['#c92a2a', '#2f9e44', '#ffd43b'],
            ],
            'newyear' => [
                'nombre'      => 'Año Nuevo',
                'icono'       => '🎆',
                'descripcion' => 'Dorado y azul medianoche',
                'decoracion'  => 'newyear',
                'auto'        => ['mesInicio' => 1, 'diaInicio' => 1, 'mesFin' => 1, 'diaFin' => 7],
                'paleta'      => ['#d4af37', '#1a1a2e', '#e94560'],
            ],
            'valentine' => [
                'nombre'      => 'San Valentín',
                'icono'       => '💝',
                'descripcion' => 'Rosas y rojos',
                'decoracion'  => 'valentine',
                'auto'        => ['mesInicio' => 2, 'diaInicio' => 10, 'mesFin' => 2, 'diaFin' => 15],
                'paleta'      => ['#e91e63', '#f8bbd0', '#c2185b'],
            ],
            'spring' => [
                'nombre'      => 'Primavera',
                'icono'       => '🌸',
                'descripcion' => 'Verdes y flores',
                'decoracion'  => null,
                'auto'        => ['mesInicio' => 3, 'diaInicio' => 20, 'mesFin' => 3, 'diaFin' => 21],
                'paleta'      => ['#66bb6a', '#f48fb1', '#aed581'],
            ],
            'summer' => [
                'nombre'      => 'Verano',
                'icono'       => '☀️',
                'descripcion' => 'Naranjas y turquesa',
                'decoracion'  => null,
                'auto'        => ['mesInicio' => 6, 'diaInicio' => 21, 'mesFin' => 6, 'diaFin' => 23],
                'paleta'      => ['#ff9800', '#00bcd4', '#ffc107'],
            ],
            'patrias' => [
                'nombre'      => 'Fiestas Patrias',
                'icono'       => '🇪🇨',
                'descripcion' => 'Amarillo, azul y rojo de Ecuador',
                'decoracion'  => null,
                'auto'        => ['mesInicio' => 8, 'diaInicio' => 5, 'mesFin' => 8, 'diaFin' => 12],
                'paleta'      => ['#ffd100', '#0033a0', '#ef3340'],
            ],
            'autumn' => [
                'nombre'      => 'Otoño',
                'icono'       => '🍂',
                'descripcion' => 'Ocres y marrones',
                'decoracion'  => null,
                'auto'        => ['mesInicio' => 9, 'diaInicio' => 22, 'mesFin' => 9, 'diaFin' => 24],
                'paleta'      => ['#d2691e', '#8b4513', '#daa520'],
            ],
            'halloween' => [
                'nombre'      => 'Halloween',
                'icono'       => '🎃',
                'descripcion' => 'Naranja calabaza y morado',
                'decoracion'  => null,
                'auto'        => ['mesInicio' => 10, 'diaInicio' => 24, 'mesFin' => 11, 'diaFin' => 2],
                'paleta'      => ['#ff7518', '#6a0dad', '#1a1a1a'],
            ],
            'blackfriday' => [
                'nombre'      => 'Black Friday',
                'icono'       => '🖤',
                'descripcion' => 'Negro y dorado, alto contraste',
                'decoracion'  => null,
                'auto'        => ['mesInicio' => 11, 'diaInicio' => 20, 'mesFin' => 12, 'diaFin' => 1],
                'paleta'      => ['#111111', '#f5c518', '#ffffff'],
            ],
            'mundial2026' => [
                'nombre'      => 'Mundial 2026',
                'icono'       => '🏆',
                'descripcion' => 'Verde césped y dorado',
                'decoracion'  => 'mundial2026',
                'auto'        => ['mesInicio' => 6, 'diaInicio' => 11, 'mesFin' => 7, 'diaFin' => 19],
                'paleta'      => ['#0b6e4f', '#e4b100', '#ffffff'],
            ],
        ];
    }

    /** ¿Existe ese identificador de tema? */
    public static function temaValido(?string $tema): bool
    {
        return $tema !== null && array_key_exists($tema, self::temas());
    }

    /**
     * Ajustes guardados, cacheados.
     *
     * Nunca lanza: si la tabla o la cache fallan (por ejemplo, en la ventana
     * entre subir los archivos y correr la migracion en produccion) devuelve
     * el respaldo, para no tumbar todas las vistas del sitio.
     */
    public function ajustes(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, function () {
                $fila = AjusteApariencia::query()->first();

                if (!$fila) {
                    $fila = AjusteApariencia::create(self::RESPALDO);
                }

                return [
                    'tema'            => $fila->tema,
                    'modo_oscuro'     => (bool) $fila->modo_oscuro,
                    'auto_temporada'  => (bool) $fila->auto_temporada,
                    'actualizado_por' => $fila->actualizado_por,
                ];
            });
        } catch (Throwable $e) {
            Log::warning('AparienciaService: usando respaldo', ['error' => $e->getMessage()]);

            return self::RESPALDO;
        }
    }

    /**
     * Tema que realmente se debe pintar.
     *
     * Si `auto_temporada` esta activo y hoy cae dentro de la ventana de un
     * tema de temporada, ese tema gana sobre el tema base. El calculo se hace
     * aqui, en el servidor, para que todos los dispositivos coincidan — antes
     * lo hacia cada navegador con su propio reloj y zona horaria.
     */
    public function temaEfectivo(): string
    {
        $ajustes = $this->ajustes();

        if ($ajustes['auto_temporada']) {
            $deTemporada = $this->temaDeTemporada();
            if ($deTemporada !== null) {
                return $deTemporada;
            }
        }

        return self::temaValido($ajustes['tema']) ? $ajustes['tema'] : 'default';
    }

    /** Tema de temporada activo hoy, o null. */
    public function temaDeTemporada(): ?string
    {
        $hoy = (int) now()->format('nd'); // p. ej. 1225 para el 25 de diciembre

        foreach (self::temas() as $id => $config) {
            if (!$config['auto']) {
                continue;
            }

            $inicio = $config['auto']['mesInicio'] * 100 + $config['auto']['diaInicio'];
            $fin    = $config['auto']['mesFin'] * 100 + $config['auto']['diaFin'];

            // Ventana normal, o ventana que cruza el fin de año.
            $dentro = $inicio <= $fin
                ? ($hoy >= $inicio && $hoy <= $fin)
                : ($hoy >= $inicio || $hoy <= $fin);

            if ($dentro) {
                return $id;
            }
        }

        return null;
    }

    /**
     * Todo lo que el layout necesita para pintar el HTML y alimentar a JS.
     */
    public function paraVista(): array
    {
        $ajustes = $this->ajustes();

        return [
            'tema'           => $this->temaEfectivo(),
            'temaBase'       => $ajustes['tema'],
            'modoOscuro'     => $ajustes['modo_oscuro'],
            'autoTemporada'  => $ajustes['auto_temporada'],
            'temaTemporada'  => $this->temaDeTemporada(),
            'actualizadoPor' => $ajustes['actualizado_por'],
            'decoracion'     => self::temas()[$this->temaEfectivo()]['decoracion'] ?? null,
            'catalogo'       => self::temas(),
        ];
    }

    /**
     * Guardar la apariencia global. Solo lo llama SistemaController (Admin).
     */
    public function guardar(array $datos, ?string $porQuien = null): array
    {
        $fila = AjusteApariencia::query()->first() ?? new AjusteApariencia();

        if (array_key_exists('tema', $datos) && self::temaValido($datos['tema'])) {
            $fila->tema = $datos['tema'];
        }

        if (array_key_exists('modo_oscuro', $datos)) {
            $fila->modo_oscuro = filter_var($datos['modo_oscuro'], FILTER_VALIDATE_BOOLEAN);
        }

        if (array_key_exists('auto_temporada', $datos)) {
            $fila->auto_temporada = filter_var($datos['auto_temporada'], FILTER_VALIDATE_BOOLEAN);
        }

        $fila->actualizado_por = $porQuien;
        $fila->save();

        Cache::forget(self::CACHE_KEY);

        return $this->paraVista();
    }
}
