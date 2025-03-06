<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Tarea;
use App\Models\Cuenta;
use App\Models\Valor;
use App\Models\Servicio;
use App\Models\Perfil;
use App\Models\Costo;
use App\Models\ViewUsuarioActivo;
use App\Models\Producto;
use App\Notifications\TareasPendientes;
use App\Services\CuentaService;
use Illuminate\Support\Facades\Notification;
use App\Models\Empleado;

class TareaService
{
    protected $cuentaService;

    public function __construct(CuentaService $cuentaService)
    {
        $this->cuentaService = $cuentaService;
    }
    public function crearOActualizarTareaQuitarUsuarios($usuarios)
    {
        $usuariosAQuitar = $this->cuentaService->obtenerUsuariosAQuitar($usuarios);
        $totales = $usuariosAQuitar->count();

        if ($usuariosAQuitar->isEmpty()) {
            return null; // No hay usuarios que quitar, no se crea la tarea
        }
        // Limitar la cantidad de usuarios en la descripción (ej. mostrar solo 10)
        $usuariosListado = $usuariosAQuitar->take(10)->map(function ($usuario) {
            return "==> [Cliente: {$usuario->cliente->nombrecli}, Cuenta: {$usuario->idcue}, Perfil: {$usuario->perfil}]";
        })->implode("\n");
        // Mensaje indicando si hay más usuarios
        $mensajeExtra = $totales > 10 ? "\n... y " . ($totales - 10) . " más usuarios." : '';

        return Tarea::updateOrCreate(
            ['nombretarea' => 'Quitar Usuarios'], // La tarea no se repite
            [
                'descripcion' => '🤖 Laravel dice: Hay ' . $totales . ' usuarios a quitar ' . $usuariosListado . $mensajeExtra,
                'prioridad' => 'alta',
                'fechalimit' => Carbon::today()->setHour(23)->setMinute(59)
            ]
        );
    }
    public function crearOActualizarTareaCobrarUsuarios($usuarios)
    {
        $usuariosAcobrar = $this->cuentaService->obtenerUsuariosACobrar($usuarios);
        $totales = $usuariosAcobrar->count();
        if ($usuariosAcobrar->isEmpty()) {
            return null; // No hay usuarios que quitar, no se crea la tarea
        }

        // Limitar la cantidad de usuarios en la descripción (ej. mostrar solo 10)
        $usuariosListado = $usuariosAcobrar->take(10)->map(function ($usuario) {
            return "=> [{$usuario->cliente->nombrecli}]";
        })->implode("\n");
        // Mensaje indicando si hay más usuarios
        $mensajeExtra = $totales > 10 ? "\n... y " . ($totales - 10) . " más usuarios." : '';

        return Tarea::updateOrCreate(
            ['nombretarea' => 'Cobrar Usuarios'], // La tarea no se repite
            [
                'descripcion' => '🤖 Laravel dice: Hay ' . $totales . ' usuarios a cobrar💵 ' . $usuariosListado . $mensajeExtra,
                'prioridad' => 'media',
                'fechalimit' => Carbon::today()->setHour(23)->setMinute(59)
            ]
        );
    }
    public function crearOActualizarTareaRenovarOEliminarCuentas($cuentas)
    {
        $cuentasPorVencer = $this->cuentaService->obtenerCuentasPorVencer($cuentas);
        $totales = $cuentasPorVencer->count();

        if ($cuentasPorVencer->isEmpty()) {
            return null; // No hay cuentas por vencer, no se crea la tarea
        }

        // Limitar la cantidad de cuentas en la descripción (ej. mostrar solo 10)
        $cuentasListado = $cuentasPorVencer->take(10)->map(function ($cuenta) {
            return "=> [{$cuenta->idcue}] vence el {$cuenta->fechavencue}";
        })->implode("\n");

        // Mensaje indicando si hay más cuentas
        $mensajeExtra = $totales > 10 ? "\n... y " . ($totales - 10) . " más cuentas." : '';

        return Tarea::updateOrCreate(
            ['nombretarea' => 'Renovar o Eliminar Cuentas'], // La tarea no se repite
            [
                'descripcion' => "🤖 Laravel dice: Hay $totales cuentas que deben renovarse o eliminarse ⏳\n$cuentasListado$mensajeExtra",
                'prioridad' => 'alta',
                'fechalimit' => Carbon::today()->setHour(23)->setMinute(59)
            ]
        );
    }
    public function crearOActualizarTareaArreglarCuentasCaidas($cuentas)
    {
        $cuentasCaidas = $this->cuentaService->obtenerCuentasCaidas($cuentas);
        $totales = $cuentasCaidas->count();

        if ($cuentasCaidas->isEmpty()) {
            return null; // No hay cuentas caídas, no se crea la tarea
        }

        // Limitar la cantidad de cuentas en la descripción (ej. mostrar solo 10)
        $cuentasListado = $cuentasCaidas->take(10)->map(function ($cuenta) {
            return "=> [{$cuenta->idcue}]";
        })->implode("\n");

        // Mensaje indicando si hay más cuentas
        $mensajeExtra = $totales > 10 ? "\n... y " . ($totales - 10) . " más cuentas caídas." : '';

        return Tarea::updateOrCreate(
            ['nombretarea' => 'Arreglar Cuentas Caídas'], // La tarea no se repite
            [
                'descripcion' => "🤖 Laravel dice: Hay $totales cuentas caídas ⚠️\n$cuentasListado$mensajeExtra",
                'prioridad' => 'alta',
                'fechalimit' => Carbon::today()->setHour(23)->setMinute(59)
            ]
        );
    }
    public function crearOActualizarTareaAjustarEspaciosClientes($cuentas)
    {
        $cuentasColapsadas = $this->cuentaService->obtenerCuentasColapsadas($cuentas);
        $totales = $cuentasColapsadas->count();

        if ($cuentasColapsadas->isEmpty()) {
            return null; // No hay cuentas colapsadas, no se crea la tarea
        }

        // Limitar la cantidad de cuentas en la descripción (ej. mostrar solo 10)
        $cuentasListado = $cuentasColapsadas->take(10)->map(function ($cuenta) {
            return "=> [{$cuenta->idcue}] (usuarios máximos permitido: {$cuenta->valor->pantmaxval})";
        })->implode("\n");

        // Mensaje indicando si hay más cuentas colapsadas
        $mensajeExtra = $totales > 10 ? "\n... y " . ($totales - 10) . " más cuentas con espacios saturados." : '';

        return Tarea::updateOrCreate(
            ['nombretarea' => 'Ajustar Espacios de Clientes'], // La tarea no se repite
            [
                'descripcion' => "🤖 Laravel dice: Hay $totales cuentas con espacios saturados 📌\n$cuentasListado$mensajeExtra",
                'prioridad' => 'baja',
                'fechalimit' => Carbon::today()->setHour(23)->setMinute(59)
            ]
        );
    }
    public function notificarEmpleadoTareas($tarea)
    {
        // Buscar empleados cuyo nombre contenga el texto de la tarea
        $empleados = Empleado::where('nombreemp', 'LIKE', "%{$tarea->nombretarea}%")->get();

        // Si no hay empleados coincidentes, seleccionar a todos los empleados
        if ($empleados->isEmpty()) {
            $empleados = Empleado::all();
        }
        // Enviar la notificación a cada empleado individualmente con su nombre
        foreach ($empleados as $empleado) {
            Notification::send($empleado, new TareasPendientes($empleado));
        }
        event(new \Illuminate\Support\Facades\Event('notificacionRecibida'));
    }
}
