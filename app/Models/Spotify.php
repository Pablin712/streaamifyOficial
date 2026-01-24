<?php

namespace App\Models;

/**
 * Modelo especializado para cuentas de Spotify
 *
 * Spotify funciona diferente a otros servicios:
 * - Perfil 1: Es la cuenta OWNER (admin) - usa usuariocue + contrasenacue
 * - Perfiles 2-6: Son invitaciones - las credenciales están en perfil.pinper
 *   El formato en pinper es: "usuario|contraseña"
 */
class Spotify extends Cuenta
{
    protected $table = 'cuentas';

    /**
     * Boot del modelo para aplicar scope global
     */
    protected static function boot()
    {
        parent::boot();

        // Filtrar solo cuentas de Spotify
        static::addGlobalScope('spotify', function ($builder) {
            $builder->whereHas('valor.servicio', function($q) {
                $q->where('idser', 'SPOTIFY');
            });
        });
    }

    /**
     * Obtener datos del perfil 1 (Owner/Admin)
     * El perfil 1 usa las credenciales principales de la cuenta
     *
     * @return array ['usuario' => string, 'contrasena' => string]
     */
    public function getPerfil1Attribute()
    {
        return [
            'usuario' => $this->usuariocue,
            'contrasena' => $this->contrasenacue,
            'tipo' => 'owner'
        ];
    }

    /**
     * Obtener datos de un perfil invitado (2-6)
     * Las credenciales están en perfiles.pinper con formato "usuario|contraseña"
     *
     * @param int $numeroPerfil Número del perfil (2-6)
     * @return array|null ['usuario' => string, 'contrasena' => string] o null si no existe
     */
    public function getPerfilInvitado($numeroPerfil)
    {
        if ($numeroPerfil < 2 || $numeroPerfil > 6) {
            return null;
        }

        $perfil = $this->perfiles()
            ->where('numeroper', $numeroPerfil)
            ->first();

        if (!$perfil || !$perfil->pinper || $perfil->pinper === 'invit') {
            return null;
        }

        // Parsear el formato "usuario|contraseña"
        $partes = explode('|', $perfil->pinper);

        if (count($partes) === 2) {
            return [
                'usuario' => $partes[0],
                'contrasena' => $partes[1],
                'tipo' => 'invitado',
                'numero_perfil' => $numeroPerfil
            ];
        }

        return null;
    }

    /**
     * Obtener todos los perfiles con sus credenciales
     *
     * @return array
     */
    public function getTodosLosPerfiles()
    {
        $perfiles = [];

        // Perfil 1 (Owner)
        $perfiles[1] = $this->perfil1;

        // Perfiles invitados (2-6)
        for ($i = 2; $i <= 6; $i++) {
            $perfilData = $this->getPerfilInvitado($i);
            if ($perfilData) {
                $perfiles[$i] = $perfilData;
            }
        }

        return $perfiles;
    }

    /**
     * Contar cuántos perfiles invitados están configurados
     *
     * @return int
     */
    public function getPerfilesInvitadosConfigurados()
    {
        return $this->perfiles()
            ->where('numeroper', '>', 1)
            ->where('pinper', '!=', 'invit')
            ->where('pinper', '!=', '')
            ->whereNull('pinper')
            ->count();
    }

    /**
     * Verificar si un perfil está disponible (sin usuario asignado)
     *
     * @param int $numeroPerfil
     * @return bool
     */
    public function perfilDisponible($numeroPerfil)
    {
        $perfil = $this->perfiles()
            ->where('numeroper', $numeroPerfil)
            ->first();

        if (!$perfil) {
            return false;
        }

        // Para perfil 1 (owner), siempre está "ocupado"
        if ($numeroPerfil === 1) {
            return false;
        }

        // Para perfiles invitados, verificar si tiene usuario activo
        $tieneUsuarioActivo = \App\Models\ViewUsuarioActivo::where('idper', $perfil->idper)
            ->where('fecha_vencimiento', '>', now())
            ->exists();

        return !$tieneUsuarioActivo;
    }

    /**
     * Obtener perfiles disponibles para asignar
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPerfilesDisponibles()
    {
        return $this->perfiles()
            ->where('numeroper', '>', 1) // Excluir owner
            ->whereDoesntHave('detalles_venta', function($q) {
                $q->where('activodet', true)
                  ->where('fechavendet', '>', now());
            })
            ->get();
    }

    /**
     * Configurar credenciales de un perfil invitado
     *
     * @param int $numeroPerfil
     * @param string $usuario
     * @param string $contrasena
     * @return bool
     */
    public function configurarPerfilInvitado($numeroPerfil, $usuario, $contrasena)
    {
        if ($numeroPerfil < 2 || $numeroPerfil > 6) {
            return false;
        }

        $perfil = $this->perfiles()
            ->where('numeroper', $numeroPerfil)
            ->first();

        if (!$perfil) {
            return false;
        }

        // Guardar en formato "usuario|contraseña"
        $perfil->pinper = "{$usuario}|{$contrasena}";
        return $perfil->save();
    }

    /**
     * Obtener mensaje formateado de entrega para Spotify
     *
     * @param int $numeroPerfil
     * @return string
     */
    public function getMensajeEntrega($numeroPerfil)
    {
        if ($numeroPerfil === 1) {
            return "🎵 *SPOTIFY PREMIUM* 🎵\n\n" .
                   "👤 *Usuario:* {$this->usuariocue}\n" .
                   "🔑 *Contraseña:* {$this->contrasenacue}\n" .
                   "📍 *Perfil:* Owner (Administrador)\n" .
                   "📅 *Vence:* " . $this->fechavencue->format('d/m/Y');
        }

        $perfilData = $this->getPerfilInvitado($numeroPerfil);

        if (!$perfilData) {
            return "Error: Perfil no configurado";
        }

        return "🎵 *SPOTIFY PREMIUM* 🎵\n\n" .
               "👤 *Usuario:* {$perfilData['usuario']}\n" .
               "🔑 *Contraseña:* {$perfilData['contrasena']}\n" .
               "📍 *Perfil:* Invitado #{$numeroPerfil}\n" .
               "📅 *Vence:* " . $this->fechavencue->format('d/m/Y');
    }

    /**
     * Scope para obtener solo cuentas completas
     */
    public function scopeCompletas($query)
    {
        return $query->where('tipo_cuenta', 'completa')
                    ->where('idcue', 'NOT LIKE', 'IND.%');
    }

    /**
     * Scope para obtener solo cuentas individuales
     */
    public function scopeIndividuales($query)
    {
        return $query->where('tipo_cuenta', 'individual')
                    ->where('idcue', 'LIKE', 'IND.%');
    }
}
