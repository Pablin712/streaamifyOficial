<?php

/**
 * EJEMPLOS DE USO DE MODELOS ESPECIALIZADOS DE CUENTAS
 *
 * Este archivo muestra cómo usar los nuevos modelos especializados
 * para cada servicio de streaming.
 */

namespace App\Examples;

use App\Models\Spotify;
use App\Models\Netflix;
use App\Models\Disney;
use App\Models\Max;
use App\Models\Prime;
use App\Models\Paramount;
use App\Models\Crunchyroll;
use App\Models\Magis;

class CuentasEspecializadasExamples
{
    /**
     * EJEMPLO 1: Crear y configurar cuenta de Spotify
     */
    public function ejemploSpotify()
    {
        // Crear cuenta completa de Spotify
        $spotify = new Spotify();
        $spotify->idval = 'SPOTIFY-PREMIUM-12M'; // ID del valor
        $spotify->tipo_cuenta = 'completa';
        $spotify->usuariocue = 'owner@spotify.com';
        $spotify->contrasenacue = 'ownerpass123';
        $spotify->fechavencue = now()->addMonths(12);
        $spotify->activocue = true;
        $spotify->caidacue = false;
        $spotify->save();

        // El trigger genera: SPOTIFY-1 (o siguiente número)
        echo "Cuenta creada: {$spotify->idcue}\n";

        // Configurar perfiles invitados (2-6)
        // Esto guarda en perfiles.pinper con formato "usuario|contraseña"
        $spotify->configurarPerfilInvitado(2, 'invitado1@mail.com', 'pass123');
        $spotify->configurarPerfilInvitado(3, 'invitado2@mail.com', 'pass456');
        $spotify->configurarPerfilInvitado(4, 'invitado3@mail.com', 'pass789');

        // Obtener datos del perfil Owner (perfil 1)
        $owner = $spotify->perfil1;
        echo "Owner - Usuario: {$owner['usuario']}, Contraseña: {$owner['contrasena']}\n";

        // Obtener datos de perfil invitado
        $invitado = $spotify->getPerfilInvitado(2);
        echo "Invitado 2 - Usuario: {$invitado['usuario']}, Contraseña: {$invitado['contrasena']}\n";

        // Obtener todos los perfiles configurados
        $todosPerfiles = $spotify->getTodosLosPerfiles();
        foreach ($todosPerfiles as $numero => $perfil) {
            echo "Perfil {$numero}: {$perfil['usuario']} ({$perfil['tipo']})\n";
        }

        // Generar mensaje de entrega para cliente
        $mensajeOwner = $spotify->getMensajeEntrega(1);
        $mensajeInvitado = $spotify->getMensajeEntrega(2);

        // Verificar perfiles disponibles
        if ($spotify->perfilDisponible(5)) {
            echo "Perfil 5 está disponible para asignar\n";
        }

        // Listar todos los perfiles disponibles
        $disponibles = $spotify->getPerfilesDisponibles();
        echo "Perfiles disponibles: " . $disponibles->count() . "\n";
    }

    /**
     * EJEMPLO 2: Trabajar con cuentas de Netflix
     */
    public function ejemploNetflix()
    {
        // Obtener todas las cuentas completas de Netflix
        $cuentasCompletas = Netflix::completas()
            ->where('activocue', true)
            ->get();

        // Obtener cuentas individuales
        $cuentasIndividuales = Netflix::individuales()
            ->where('activocue', true)
            ->get();

        // Trabajar con una cuenta específica
        $netflix = Netflix::find('NETFLIX-1');

        if ($netflix) {
            // Obtener perfiles con sus PINs
            $perfiles = $netflix->getPerfilesConPin();

            foreach ($perfiles as $perfil) {
                echo "Perfil {$perfil['numero']}:\n";
                echo "  - PIN: {$perfil['pin']}\n";
                echo "  - Usuario: {$perfil['usuario']}\n";
                echo "  - Contraseña: {$perfil['contrasena']}\n";
                echo "  - Disponible: " . ($perfil['disponible'] ? 'Sí' : 'No') . "\n\n";
            }

            // Buscar primer perfil disponible
            $perfilDisponible = $perfiles->firstWhere('disponible', true);
            if ($perfilDisponible) {
                echo "Perfil {$perfilDisponible['numero']} disponible con PIN {$perfilDisponible['pin']}\n";
            }
        }
    }

    /**
     * EJEMPLO 3: Disney Premium vs Standard
     */
    public function ejemploDisney()
    {
        // Obtener solo cuentas Disney Premium (7 perfiles)
        $premium = Disney::premium()
            ->where('activocue', true)
            ->get();

        // Obtener solo cuentas Disney Standard (5 perfiles)
        $standard = Disney::standard()
            ->where('activocue', true)
            ->get();

        // Trabajar con una cuenta específica
        $disney = Disney::find('DISNEYP-1');

        if ($disney) {
            // Verificar tipo
            if ($disney->isPremium()) {
                echo "Esta es una cuenta Premium con {$disney->getMaxPerfiles()} perfiles\n";
            } else {
                echo "Esta es una cuenta Standard con {$disney->getMaxPerfiles()} perfiles\n";
            }

            // Obtener perfiles disponibles
            $disponibles = $disney->perfiles()
                ->whereDoesntHave('detalles_venta', function($q) {
                    $q->where('activodet', true);
                })
                ->get();

            echo "Perfiles disponibles: " . $disponibles->count() . "\n";
        }
    }

    /**
     * EJEMPLO 4: Crear cuentas automáticamente
     */
    public function ejemploCreacionAutomatica()
    {
        // Crear cuenta completa - El ID se genera automáticamente
        $max = new Max();
        $max->idval = 'MAX-STANDARD-12M';
        $max->tipo_cuenta = 'completa';
        $max->usuariocue = 'user@hbomax.com';
        $max->contrasenacue = 'password';
        $max->fechavencue = now()->addYear();
        $max->activocue = true;
        $max->save();
        // ID generado: MAX-1 (o siguiente)

        // Crear cuenta individual
        $maxIndividual = new Max();
        $maxIndividual->idval = 'MAX-STANDARD-12M';
        $maxIndividual->tipo_cuenta = 'individual';
        $maxIndividual->usuariocue = 'individual@hbomax.com';
        $maxIndividual->contrasenacue = 'pass123';
        $maxIndividual->fechavencue = now()->addYear();
        $maxIndividual->activocue = true;
        $maxIndividual->save();
        // ID generado: IND.MAX-1 (o siguiente)

        echo "Cuenta completa: {$max->idcue}\n";
        echo "Cuenta individual: {$maxIndividual->idcue}\n";
    }

    /**
     * EJEMPLO 5: Filtrar cuentas por tipo
     */
    public function ejemploFiltros()
    {
        // Todas las cuentas completas de Prime
        $primesCompletas = Prime::completas()->get();

        // Todas las cuentas individuales de Crunchyroll
        $crunchyIndividuales = Crunchyroll::individuales()->get();

        // Cuentas activas de Paramount
        $paramountActivas = Paramount::where('activocue', true)
            ->where('fechavencue', '>', now())
            ->get();

        // Cuentas que no son mesa de trabajo
        $magisNormales = Magis::where('idcue', 'NOT LIKE', '%Atencion')->get();

        // Combinación de scopes
        $netflixCompletasActivas = Netflix::completas()
            ->where('activocue', true)
            ->where('caidacue', false)
            ->with(['perfiles', 'valor.servicio'])
            ->get();
    }

    /**
     * EJEMPLO 6: Migración de código legacy
     */
    public function ejemploMigracionLegacy()
    {
        // Código antiguo (sigue funcionando)
        $cuenta = Cuenta::find('SPOTIFY-1');

        // Nuevo código (más específico)
        $spotify = Spotify::find('SPOTIFY-1');

        // Convertir de Cuenta genérica a Spotify
        if ($cuenta && $cuenta->valor->servicio->idser === 'SPOTIFY') {
            $spotify = Spotify::find($cuenta->idcue);
            // Ahora puedes usar métodos específicos de Spotify
            $perfiles = $spotify->getTodosLosPerfiles();
        }
    }

    /**
     * EJEMPLO 7: Búsqueda inteligente
     */
    public function ejemploBusqueda()
    {
        // Buscar cuentas con espacio disponible
        $spotifyConEspacio = Spotify::whereHas('perfiles', function($q) {
            $q->whereDoesntHave('detalles_venta', function($dv) {
                $dv->where('activodet', true);
            });
        })->get();

        // Buscar cuentas por vencer (próximos 7 días)
        $netflixPorVencer = Netflix::where('activocue', true)
            ->whereBetween('fechavencue', [now(), now()->addDays(7)])
            ->get();

        // Buscar cuentas con usuarios activos
        $maxConUsuarios = Max::whereHas('usuarios')
            ->with(['usuarios' => function($q) {
                $q->where('fecha_vencimiento', '>', now());
            }])
            ->get();
    }

    /**
     * EJEMPLO 8: Estadísticas por servicio
     */
    public function ejemploEstadisticas()
    {
        // Contar cuentas por tipo
        $stats = [
            'spotify_completas' => Spotify::completas()->count(),
            'spotify_individuales' => Spotify::individuales()->count(),
            'netflix_activas' => Netflix::where('activocue', true)->count(),
            'disney_premium' => Disney::premium()->count(),
            'disney_standard' => Disney::standard()->count(),
        ];

        // Capacidad disponible de Spotify
        $spotifyTotal = Spotify::count();
        $perfilesDisponibles = 0;
        Spotify::all()->each(function($cuenta) use (&$perfilesDisponibles) {
            $perfilesDisponibles += $cuenta->getPerfilesDisponibles()->count();
        });

        echo "Total cuentas Spotify: {$spotifyTotal}\n";
        echo "Perfiles disponibles: {$perfilesDisponibles}\n";
    }

    /**
     * EJEMPLO 9: Manejo de errores
     */
    public function ejemploManejolloErrores()
    {
        try {
            $spotify = Spotify::findOrFail('SPOTIFY-999');
        } catch (\Exception $e) {
            echo "Cuenta no encontrada\n";
        }

        // Verificar antes de usar
        $spotify = Spotify::find('SPOTIFY-1');
        if ($spotify) {
            // Verificar que perfil existe antes de configurar
            $resultado = $spotify->configurarPerfilInvitado(2, 'user@mail.com', 'pass');
            if ($resultado) {
                echo "Perfil configurado correctamente\n";
            } else {
                echo "Error configurando perfil\n";
            }
        }

        // Verificar perfil disponible antes de asignar
        if ($spotify && $spotify->perfilDisponible(3)) {
            // Asignar cliente al perfil 3
        } else {
            echo "Perfil no disponible\n";
        }
    }
}
