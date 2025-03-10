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

class CuentaService
{
    public function calcularEspaciosPorServicio()
    {
        $servicios = ['NETFLIX', 'DISNEYP', 'DISNEYS', 'MAX', 'PRIME', 'PARAMOUNT', 'CRUNCHY', 'SPOTIFY', 'MAGIS'];
        $espacios_por_servicio = [];

        foreach ($servicios as $servicio) {
            // Obtener todas las cuentas activas que pertenezcan a este servicio
            $cuentas = Cuenta::with(['valor.servicio']) // Cargamos servicio a través de valor
                ->whereHas('valor.servicio', function ($query) use ($servicio) {
                    $query->where('idser', $servicio); // Filtrar por nombre del servicio
                })
                ->where('activocue', true)
                ->orderBy('fechavencue')
                ->get();
            $espacios = 0;
            foreach ($cuentas as $cuenta) {
                $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->where('fecha_vencimiento', '>', now())->count();
                $pantmaxval = $cuenta->valor->pantmaxval ?? 0; // Verificamos que el valor no sea nulo
                $resta = $pantmaxval - $usuarios;
                $espacios += max($resta, 0); // Evitamos valores negativos
            }
            // Guardar el total de espacios disponibles para este servicio
            $espacios_por_servicio[$servicio] = $espacios;
        }
        return $espacios_por_servicio;
    }
    public function calcularEspaciosTotales()
    {
        // Obtener todas las cuentas activas
        $cuentas = Cuenta::with(['valor']) // Cargamos la relación con valor
            ->where('activocue', true)
            ->orderBy('fechavencue')
            ->get();
        $espacios_totales = 0;
        foreach ($cuentas as $cuenta) {
            // Contar los usuarios activos en la cuenta con fecha de vencimiento futura
            $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)
                ->where('fecha_vencimiento', '>', now())
                ->count();
            // Obtener la cantidad máxima de pantallas permitidas
            $pantmaxval = $cuenta->valor->pantmaxval ?? 0;
            // Calcular los espacios disponibles en la cuenta
            $espacios_disponibles = max($pantmaxval - $usuarios, 0);
            // Sumar a la cantidad total
            $espacios_totales += $espacios_disponibles;
        }
        return $espacios_totales;
    }
    public function buscarCuentaDisponible($idser)
    {
        return Cuenta::whereHas('valor', function ($query) use ($idser) {
            $query->where('idser', $idser);
        })
            ->where('caidacue', false)
            ->where('activocue', true)
            ->whereHas('valor', function ($query) {
                $query->whereRaw('(SELECT COUNT(*) FROM view_usuarios_activos WHERE view_usuarios_activos.idcue = cuentas.idcue) < valores.pantmaxval');
            })
            ->first();
    }
    public function actualizarEstadoProductos($idser)
    {
        $productos = Producto::where('tipo_producto_id', 1)
            ->whereHas('detalles', function ($query) use ($idser) {
                $query->where('idser', $idser);
            })->get();

        foreach ($productos as $producto) {
            $cuentaDisponible = $this->buscarCuentaDisponible($idser);
            $producto->update(['activo' => $cuentaDisponible ? true : false]);
        }
    }
    public function generarNuevoId($idcue)
    {
        $baseId = preg_replace('/_borrada\d*$/', '', $idcue);
        $contador = 1;

        $ultimoId = Cuenta::where('idcue', 'LIKE', "{$baseId}_borrada%")
            ->orderByRaw("LENGTH(idcue) DESC")
            ->orderBy('idcue', 'DESC')
            ->pluck('idcue')
            ->first();

        if ($ultimoId) {
            preg_match('/_borrada(\d+)$/', $ultimoId, $matches);
            if (!empty($matches[1])) {
                $contador = (int) $matches[1] + 1;
            }
        }

        return "{$baseId}_borrada{$contador}";
    }
    public function generarNuevoIdPerfil($idper)
    {
        $baseId = preg_replace('/_borrada\d*$/', '', $idper);
        $contador = 1;

        $ultimoId = Perfil::where('idper', 'LIKE', "{$baseId}_borrada%")
            ->orderByRaw("LENGTH(idper) DESC")
            ->orderBy('idper', 'DESC')
            ->pluck('idper')
            ->first();

        if ($ultimoId) {
            preg_match('/_borrada(\d+)$/', $ultimoId, $matches);
            if (!empty($matches[1])) {
                $contador = (int) $matches[1] + 1;
            }
        }

        return "{$baseId}_borrada{$contador}";
    }
    public function contarCuentasCaidas($cuentas)
    {
        return $cuentas->filter(fn($cuenta) => $cuenta->caidacue == true)->count();
    }
    public function obtenerCuentasCaidas($cuentas)
    {
        return $cuentas->filter(fn($cuenta) => $cuenta->caidacue == true);
    }
    public function contarUsuariosACobrar($usuarios)
    {
        $usuariosACobrar = 0;
        $hoy = Carbon::today();
        foreach ($usuarios as $usuario) {
            $fechaVencimiento = Carbon::parse($usuario->fecha_vencimiento);
            $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);

            if ($diasRestantes <= 3) {
                $usuariosACobrar++;
            }
        }
        return $usuariosACobrar;
    }
    public function obtenerUsuariosACobrar($usuarios)
    {
        $hoy = Carbon::today();
        return $usuarios->filter(function ($usuario) use ($hoy) {
            $fechaVencimiento = Carbon::parse($usuario->fecha_vencimiento);
            return $hoy->diffInDays($fechaVencimiento, false) <= 3;
        });
    }
    public function obtenerUsuariosAQuitar($usuarios)
    {
        $hoy = Carbon::today();
        return $usuarios->filter(function ($usuario) use ($hoy) {
            $fechaVencimiento = Carbon::parse($usuario->fecha_vencimiento);
            return $hoy->diffInDays($fechaVencimiento, false) <= 0;
        });
    }
    public function contarCuentasActivas($cuentas)
    {
        return $cuentas->count();
    }
    public function asignarUsuarios($cuentas)
    {
        foreach ($cuentas as $cuenta) {
            $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)
                ->where('fecha_vencimiento', '>', now())
                ->count();
            $cuenta->usuarios_activos = $usuarios;
        }
    }
    //Es necesario que el metodo asignar usuarios se ejecute antes de contar cuentas colapsadas
    public function contarCuentasColapsadas($cuentas)
    {
        return $cuentas->filter(function ($cuenta) {
            return $cuenta->valor->pantmaxval < $cuenta->usuarios_activos;
        })->count();
    }
    public function obtenerCuentasColapsadas($cuentas)
    {
        $this->asignarUsuarios($cuentas);
        return $cuentas->filter(function ($cuenta) {
            return $cuenta->valor->pantmaxval < $cuenta->usuarios_activos;
        });
    }
    public function obtenerCuentasSinOcupar($cuentas)
    {
        return $cuentas->filter(function ($cuenta) {
            return $cuenta->valor->pantminval > $cuenta->usuarios_activos;
        });
    }
    public function obtenerCuentasPorVencer($cuentas)
    {
        return $cuentas->filter(function ($cuenta) {
            $fechaVencimiento = Carbon::parse($cuenta->fechavencue)->startOfDay(); // Asegurar que la fecha es sin hora
            $hoy = now()->startOfDay();
            $cincoDiasDespues = $hoy->copy()->addDays(5);

            return $fechaVencimiento->lessThanOrEqualTo($cincoDiasDespues);
        });
    }
    public function obtenerCuentasDisponibles($cuentas)
    {
        return $cuentas->filter(function ($cuenta) {
            return !$cuenta->caidacue // No esté caída
                && ($cuenta->valor->pantmaxval >= $cuenta->usuarios_activos); // No esté colapsada
        });
    }
    public function calcularUsuariosPorPerfil($cuenta)
    {
        $perfiles = Perfil::where('idcue', $cuenta->idcue)->orderBy('numeroper')->get();
        foreach ($perfiles as $perfil) {
            $usuariosActivos = ViewUsuarioActivo::where('perfil', $perfil->numeroper)
                ->where('idcue', $cuenta->idcue)
                ->count();
            $perfil->usuarios_activos = $usuariosActivos;
        }
        return $perfiles;
    }
}
