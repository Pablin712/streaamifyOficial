<?php

namespace App\Services;

use App\Models\AjusteApariencia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Apariencia de la plataforma. Hay DOS cosas distintas y no se mezclan:
 *
 *  1. EL TEMA (Navidad, Neon, Mundial, Oceano...) es GLOBAL. Lo fija el
 *     administrador en /admin/sistema y lo ve todo el mundo: empleados en
 *     cualquier dispositivo y clientes en el sitio publico. Vive en la tabla
 *     ajustes_apariencia. Antes vivia en localStorage y por eso no se
 *     propagaba a nadie.
 *
 *  2. EL MODO CLARO / OSCURO es PERSONAL, como en cualquier aplicacion.
 *     Cada empleado elige entre 'system' (seguir al sistema operativo),
 *     'light' o 'dark', y se guarda en su propia fila de empleados para que
 *     le siga entre dispositivos. Los visitantes anonimos no tienen cuenta:
 *     siguen al sistema operativo, con un ajuste local opcional.
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
        'auto_temporada'  => true,
        'actualizado_por' => null,
    ];

    /** Valores admitidos para la preferencia personal de modo claro/oscuro. */
    public const ESQUEMAS = ['system', 'light', 'dark'];

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
    /** Memoria dentro de la peticion: evita repetir la consulta y el aviso. */
    private ?array $ajustesMemo = null;

    /** El aviso de respaldo se escribe una vez por proceso, no por llamada. */
    private static bool $avisoEmitido = false;

    public function ajustes(): array
    {
        // paraVista() consulta los ajustes dos veces (directamente y a traves
        // de temaEfectivo). Sin esta memoria eran dos consultas y, cuando la
        // base fallaba, dos avisos en el log por cada peticion: 823 lineas en
        // un solo dia sobre un laravel.log de 400 MB.
        if ($this->ajustesMemo !== null) {
            return $this->ajustesMemo;
        }

        try {
            return $this->ajustesMemo = Cache::rememberForever(self::CACHE_KEY, function () {
                $fila = AjusteApariencia::query()->first();

                if (!$fila) {
                    $fila = AjusteApariencia::create(self::RESPALDO);
                }

                return [
                    'tema'            => $fila->tema,
                    'auto_temporada'  => (bool) $fila->auto_temporada,
                    'actualizado_por' => $fila->actualizado_por,
                ];
            });
        } catch (Throwable $e) {
            // Una sola linea por proceso: el mensaje se repetia identico cientos
            // de veces al dia y engordaba el log sin aportar nada nuevo.
            if (!self::$avisoEmitido) {
                self::$avisoEmitido = true;
                Log::warning('AparienciaService: usando apariencia por defecto', [
                    'error' => substr($e->getMessage(), 0, 120),
                ]);
            }

            return $this->ajustesMemo = self::RESPALDO;
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
        $tema    = $this->temaEfectivo();

        return [
            // --- Global: lo fija el administrador, lo ve todo el mundo ---
            'tema'           => $tema,
            'temaBase'       => $ajustes['tema'],
            'autoTemporada'  => $ajustes['auto_temporada'],
            'temaTemporada'  => $this->temaDeTemporada(),
            'actualizadoPor' => $ajustes['actualizado_por'],
            'decoracion'     => self::temas()[$tema]['decoracion'] ?? null,
            'catalogo'       => self::temas(),

            // --- Personal: preferencia de quien esta mirando ---
            'esquema'        => $this->esquemaDe(),
        ];
    }

    /**
     * Preferencia personal de modo claro/oscuro.
     *
     * Devuelve 'system', 'light' o 'dark'. Para un empleado autenticado sale
     * de su fila; para un visitante anonimo es 'system' y el navegador puede
     * afinarlo localmente.
     *
     * 'system' NO se resuelve aqui a claro u oscuro: solo el navegador sabe
     * como tiene configurado el sistema operativo quien esta mirando. Lo
     * resuelve el script en linea del layout, antes del primer pintado.
     */
    public function esquemaDe(): string
    {
        try {
            $usuario = auth()->user();
        } catch (Throwable $e) {
            return 'system';
        }

        $preferencia = $usuario->preferencia_tema ?? null;

        return in_array($preferencia, self::ESQUEMAS, true) ? $preferencia : 'system';
    }

    /**
     * Guardar la preferencia PERSONAL de quien esta autenticado.
     * No toca la apariencia de nadie mas.
     */
    public function guardarEsquema(string $esquema): string
    {
        if (!in_array($esquema, self::ESQUEMAS, true)) {
            $esquema = 'system';
        }

        $usuario = auth()->user();

        if ($usuario) {
            $usuario->preferencia_tema = $esquema;
            $usuario->save();
        }

        return $esquema;
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

        if (array_key_exists('auto_temporada', $datos)) {
            $fila->auto_temporada = filter_var($datos['auto_temporada'], FILTER_VALIDATE_BOOLEAN);
        }

        $fila->actualizado_por = $porQuien;
        $fila->save();

        Cache::forget(self::CACHE_KEY);
        $this->ajustesMemo = null;

        return $this->paraVista();
    }
}
