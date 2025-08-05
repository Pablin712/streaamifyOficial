<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Cuenta;
use App\Models\Valor;
use App\Models\Servicio;
use App\Models\Perfil;
use App\Models\Costo;
use App\Models\DetalleVenta;
use App\Models\Historial;
use App\Models\ViewUsuarioActivo;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;

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
                ->where('caidacue', false)
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
    public function obtenerCuentasPorServicioSinFiltros($idser)
    {
        //no tiene filtro caidacue, contando usuarios activos
        //ver permiso de usuario si puede ver todas las cuentas o spotify
        
        return Cuenta::with(['valor.servicio'])
            ->whereHas('valor.servicio', function ($query) use ($idser) {
                $query->where('idser', $idser);
            })
            ->where('activocue', true)
            ->orderBy('fechavencue')
            ->get();
    }
    public function buscarPerfilDisponible($cuenta)
    {
        if (!$cuenta) {
            return null;
        }
        // Primero, intenta encontrar un perfil con 0 usuarios activos
        $perfil = Perfil::where('idcue', $cuenta->idcue)
            ->whereRaw('(SELECT COUNT(*) FROM view_usuarios_activos 
                    WHERE view_usuarios_activos.idcue = perfiles.idcue 
                    AND view_usuarios_activos.perfil = perfiles.numeroper) = 0')
            ->first();

        // Si no hay perfiles con 0 usuarios, intenta encontrar uno con solo 1 usuario activo
        if (!$perfil) {
            $perfil = Perfil::where('idcue', $cuenta->idcue)
                ->whereRaw('(SELECT COUNT(*) FROM view_usuarios_activos 
                        WHERE view_usuarios_activos.idcue = perfiles.idcue 
                        AND view_usuarios_activos.perfil = perfiles.numeroper) = 1')
                ->first();
        }
        return $perfil; // Retorna el perfil encontrado o null si no hay ninguno disponible
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
    public function generarNuevoIdValor($idval)
    {
        $baseId = preg_replace('/_era\d*$/', '', $idval);
        $contador = 1;

        $ultimoId = Valor::where('idval', 'LIKE', "{$baseId}_era%")
            ->orderByRaw("LENGTH(idval) DESC")
            ->orderBy('idval', 'DESC')
            ->pluck('idval')
            ->first();

        if ($ultimoId) {
            preg_match('/_era(\d+)$/', $ultimoId, $matches);
            if (!empty($matches[1])) {
                $contador = (int) $matches[1] + 1;
            }
        }

        return "{$baseId}_era{$contador}";
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
        $cuentas = $cuentas->filter(function ($cuenta) {
            return !str_ends_with($cuenta->idcue, 'Atencion');
        });
        return $cuentas->filter(fn($cuenta) => $cuenta->caidacue == true)->count();
    }
    public function obtenerCuentas(){
        $cuentas = Cuenta::with(['valor.servicio'])
            ->where('activocue', true)
            ->orderBy('fechavencue')
            ->get();
        $cuentas = $cuentas->filter(function ($cuenta) {
            return !str_ends_with($cuenta->idcue, 'Atencion');
        });
        return $cuentas;
    }
    public function obtenerCuentasCaidas($cuentas)
    {
        // excluir la que tienen de sufijo o terminan con la palabra: atencion
        $cuentas = $cuentas->filter(function ($cuenta) {
            return !str_ends_with($cuenta->idcue, 'Atencion');
        });
        return $cuentas->filter(fn($cuenta) => $cuenta->caidacue == true);
    }
    public function obtenerMesasDeTrabajo()
    {
        $cuentas = Cuenta::with(['valor.servicio'])
            ->where('activocue', true)
            ->orderBy('fechavencue')
            ->get();
        // Obtener todas las cuentas que terminan con 'Atencion'
        return $cuentas->filter(function ($cuenta) {
            return str_ends_with($cuenta->idcue, 'Atencion');
        });
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
                && ($cuenta->valor->pantmaxval > $cuenta->usuarios_activos); // No esté colapsada
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
    public function obtenerCuentasSegunPermiso($usuario)
    {
        // Si el usuario tiene permiso para ver todas las cuentas
        if ($usuario->can('todas_las_cuentas')) {
            return $this->obtenerCuentas();
        }
        // Verificar si el usuario tiene al menos un permiso específico
        $tienePermiso = $usuario->can('netflix') ||
            $usuario->can('disney') ||
            $usuario->can('max') ||
            $usuario->can('spotify') ||
            $usuario->can('prime') ||
            $usuario->can('otras');

        // Si no tiene ningún permiso, devolver una colección vacía
        if (!$tienePermiso) {
            return collect(); // Retorna una colección vacía
        }
        // Filtrar cuentas según permisos específicos
        $query = Cuenta::with(['valor.servicio'])
            ->where('activocue', true)
            ->whereHas('valor.servicio', function ($query) use ($usuario) {
                $query->where(function ($query) use ($usuario) {
                    if ($usuario->can('netflix')) {
                        $query->orWhere('nombreser', 'like', '%Netflix%');
                    }
                    if ($usuario->can('disney')) {
                        $query->orWhere('nombreser', 'like', '%Disney%');
                    }
                    if ($usuario->can('max')) {
                        $query->orWhere('nombreser', 'like', '%Max%');
                    }
                    if ($usuario->can('spotify')) {
                        $query->orWhere('nombreser', 'like', '%Spotify%');
                    }
                    if ($usuario->can('prime')) {
                        $query->orWhere('nombreser', 'like', '%Prime%');
                    }
                    if ($usuario->can('otras')) {
                        $query->orWhere('nombreser', 'not like', '%Netflix%')
                            ->where('nombreser', 'not like', '%Disney%')
                            ->where('nombreser', 'not like', '%Max%')
                            ->where('nombreser', 'not like', '%Spotify%')
                            ->where('nombreser', 'not like', '%Prime%');
                    }
                });
            });
        $query = $query->filter(function ($cuenta) {
            return !str_ends_with($cuenta->idcue, 'Atencion');
        });
        return $query->orderBy('fechavencue')->get();
    }
    public function obtenerCuentaDestino($cuentaOrigen)
    {
        if (!$cuentaOrigen) {
            return null; // Si la cuenta origen no existe, retornar null
        }
        $IDcuentaDestino = $cuentaOrigen->valor->idser . '-Atencion';
        $cuentaDestino = Cuenta::find($IDcuentaDestino);
        if (!$cuentaDestino) {
            $cuentaDestino = new Cuenta();
            $cuentaDestino->idcue = $IDcuentaDestino;
            $cuentaDestino->activocue = true;
            $cuentaDestino->caidacue = true;
            $cuentaDestino->usuariocue = 'Atencion'; // Asignar un usuario por defecto
            $cuentaDestino->contrasenacue = 'Atencion'; // Asignar una contraseña por defecto
            $cuentaDestino->fechavencue = now()->addYear(); // Asignar una fecha de vencimiento por defecto
            $cuentaDestino->valor()->associate($cuentaOrigen->valor); // Asociar el mismo valor
            $cuentaDestino->save();
        }
        return $cuentaDestino;
    }
    public function moverClientesDeCuenta($cuentaOrigen, $cuentaDestino)
    {
        if (!$cuentaOrigen || !$cuentaDestino) {
            return false; // Si alguna cuenta no existe, retornar false
        }

        // Obtener los usuarios activos de la cuenta origen
        $usuarios = ViewUsuarioActivo::where('idcue', $cuentaOrigen->idcue)->get();
        if ($usuarios->isEmpty()) {
            return false; // Si no hay usuarios para mover, retornar false
        }

        foreach ($usuarios as $usuario) {
            // Buscar el perfil en la cuenta destino con el mismo número de perfil
            $perfilDestino = Perfil::where('idcue', $cuentaDestino->idcue)
                ->where('numeroper', $usuario->perfil)
                ->first();

            if ($perfilDestino) {
                // Actualizar el idper en DetalleVenta para este usuario
                DetalleVenta::where('iddet', $usuario->iddet)
                    ->update(['idper' => $perfilDestino->idper]);
            }
            // Si no existe el perfil en la cuenta destino, puedes decidir ignorar o manejarlo de otra forma
        }
        return true; // Retornar true si se movieron los clientes exitosamente
    }

    public function mudarClientesAOtraCuenta($cuentaOrigen)
    {
        $idser = $cuentaOrigen->valor->idser;
        $mensaje = '';
        // Obtener los usuarios activos de la cuenta origen
        $usuarios = ViewUsuarioActivo::where('idcue', $cuentaOrigen->idcue)->orderBy('nombre_cliente')->get();
        if ($usuarios->isEmpty()) {
            return 'null'; // Si no hay usuarios para mover, retornar vacio
        }
        foreach ($usuarios as $usuario) {
            // Buscar el perfil en la cuenta destino con el mismo número de perfil
            $cuentaDestino = $this->buscarCuentaDisponible($idser);
            $perfilDestino = $this->buscarPerfilDisponible($cuentaDestino);

            if ($perfilDestino) {
                // Actualizar el idper en DetalleVenta para este usuario
                DetalleVenta::where('iddet', $usuario->iddet)
                    ->update(['idper' => $perfilDestino->idper]);
                Historial::create([
                    'accion' => 'Mudacion-Usuarios',
                    'descripcion' => 'Se movió el cliente ' . $usuario->nombre_cliente . ' a la cuenta ' . $usuario->idcue . ' perfil: ' . $usuario->perfil,
                    'empleado_id' => Auth::user()->idemp,
                    'created_at' => now(),
                ]);
                $mensaje .= 'Cliente ' . $usuario->nombre_cliente . ' movido a la cuenta ' .
                 $cuentaDestino->valor->servicio->nombreser . ' Usuario: ' . $cuentaDestino->usuariocue .
                 ' Clave: ' . $cuentaDestino->contrasenacue . ' PIN de Perfil ' . $usuario->perfil . ': ' .$usuario->profile->pinper.'\n';
            } else {
                return 'incompleto \n' . $mensaje; // Retornar incompleto si no se pudo mover algún usuario
            }
            // Si no existe el perfil en la cuenta destino, puedes decidir ignorar o manejarlo de otra forma
        }
        return 'sucess \n' . $mensaje; // Retornar success si se movieron todos los usuarios exitosamente
    }
    public function mudarClienteAOtraCuenta($usuario)
    {
        $cuentaOrigen = $usuario->cuenta;
        $idser = $cuentaOrigen->valor->idser;
        $mensaje = '';
        $cuentaDestino = $this->buscarCuentaDisponible($idser);
        $perfilDestino = $this->buscarPerfilDisponible($cuentaDestino);

        if ($perfilDestino) {
            // Actualizar el idper en DetalleVenta para este usuario
            DetalleVenta::where('iddet', $usuario->iddet)
                ->update(['idper' => $perfilDestino->idper]);
            Historial::create([
                'accion' => 'Mudacion-Usuario',
                'descripcion' => 'Se movió el cliente ' . $usuario->nombre_cliente . ' a la cuenta ' . $usuario->idcue . ' perfil: ' . $usuario->perfil,
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);
            $mensaje .= 'Cliente ' . $usuario->nombre_cliente . ' movido a la cuenta ' .
                $cuentaDestino->valor->servicio->nombreser . ' Usuario: ' . $cuentaDestino->usuariocue .
                ' Clave: ' . $cuentaDestino->contrasenacue . ' PIN de Perfil ' . $usuario->perfil . ': ' . $usuario->profile->pinper;
            return $mensaje; // Retornar mensaje de éxito
        } else {
            return 'error';
        }
    }
    public function mudarClienteAMesaDeTrabajo($usuario)
    {
        $cuentaOrigen = $usuario->cuenta;
        $cuentaDestino = $this->obtenerCuentaDestino($cuentaOrigen);
        if ($cuentaDestino) {
            $perfilDestino = Perfil::where('idcue', $cuentaDestino->idcue)
                ->where('numeroper', $usuario->perfil)
                ->first();
            if ($perfilDestino) {
                // Actualizar el idper en DetalleVenta para este usuario
                DetalleVenta::where('iddet', $usuario->iddet)
                    ->update(['idper' => $perfilDestino->idper]);
                Historial::create([
                    'accion' => 'Mudacion-Usuario',
                    'descripcion' => 'Se movió el cliente ' . $usuario->nombre_cliente . ' a la cuenta de atención al cliente',
                    'empleado_id' => Auth::user()->idemp,
                    'created_at' => now(),
                ]);
                return 'Cliente ' . $usuario->nombre_cliente . ' movido a la mesa de trabajo';
            } else {
                return 'error';
            }
        } else {
            return 'error';
        }
    }
}
