<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Cuenta;
use App\Models\Valor;
use App\Models\Servicio;
use App\Models\Perfil;
use App\Models\Costo;
use App\Models\ViewUsuarioActivo;
use App\Models\Producto;

class ValorService
{
    public function obtenerServiciosPrincipales($servicios)
    {
        // Configuración de los servicios importantes
        $principales = [
            'NETFLIX' => ['color' => 'danger', 'icon' => 'logo_netflix.png', 'nombre' => 'Netflix'],
            'DISNEYP' => ['color' => 'primary', 'icon' => 'espn.jpg', 'nombre' => 'Disney+ Premium'],
            'DISNEYS' => ['color' => 'primary', 'icon' => 'disneyP.jpg', 'nombre' => 'Disney+ Standard'],
            'MAX' => ['color' => 'info', 'icon' => 'max.jpg', 'nombre' => 'HBO Max'],
            'PRIME' => ['color' => 'success', 'icon' => 'fa-amazon', 'nombre' => 'Amazon Prime'],
            'PARAMOUNT' => ['color' => 'primary', 'icon' => 'paramount.jpg', 'nombre' => 'Paramount+'],
            'CRUNCHY' => ['color' => 'warning', 'icon' => 'crunchy.jpg', 'nombre' => 'Crunchyroll'],
            'SPOTIFY' => ['color' => 'success', 'icon' => 'fa-spotify', 'nombre' => 'Spotify'],
            'MAGIS' => ['color' => 'dark', 'icon' => 'magis.jpg', 'nombre' => 'Magis TV'],
        ];

        // Filtrar y enriquecer servicios con color e ícono
        $serviciosFiltrados = $servicios->filter(function ($servicio) use ($principales) {
            return isset($principales[$servicio->idser]); // Usa el código como clave
        })->map(function ($servicio) use ($principales) {
            $config = $principales[$servicio->idser];
            $servicio->color = $config['color'];
            $servicio->icon = $config['icon'];
            $servicio->nombre_configurado = $config['nombre'];
            return $servicio;
        });

        return $serviciosFiltrados;
    }

    public function obtenerValoresDeServiciosPrincipales($valores)
    {
        $serviciosPrincipales = $this->obtenerServiciosPrincipales(Servicio::all());
        // Obtener los IDs de los servicios principales
        $idsServiciosPrincipales = $serviciosPrincipales->pluck('idser')->toArray();
        // Filtrar los valores para incluir solo los de los servicios principales
        $valoresFiltrados = $valores->filter(function ($valor) use ($idsServiciosPrincipales) {
            return in_array($valor->idser, $idsServiciosPrincipales);
        });

        return $valoresFiltrados;
    }
}
