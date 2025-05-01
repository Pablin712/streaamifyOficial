<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\Asistencia;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TareasPendientes;
use App\Models\Tarea;
use App\Models\Cuenta;
use App\Models\Valor;
use App\Models\Servicio;
use App\Models\Perfil;
use App\Models\Costo;
use App\Models\ViewUsuarioActivo;

class EmpleadoService
{
    public function obtenerAsistenciasPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Obtener todas las asistencias del empleado para el día, mes y año especificados
        return Asistencia::where('empleado_id', $idemp)
            ->whereDate('created_at', $fecha)
            ->get();
    }
    public function obtenerLapsosDeAsistenciasPorDia(int $idemp, string $fecha)
    {
        $asistencias = $this->obtenerAsistenciasPorDia($idemp, $fecha);
        $lapsos = [];
        $total_conexion = 0;

        if (count($asistencias) === 0) {
            return [
                'lapsos' => [],
                'total_conexion' => 0
            ];
        }

        $inicio = $asistencias[0]->created_at;
        $fin = $asistencias[0]->created_at;
        $tiempo_conexion = 0;

        for ($i = 1; $i < count($asistencias); $i++) {
            $anterior = $asistencias[$i - 1]->created_at;
            $actual = $asistencias[$i]->created_at;

            if ($actual->diffInMinutes($anterior) <= 5) {
                $fin = $actual;
                $tiempo_conexion += 5;
            } else {
                $lapsos[] = [
                    'inicio' => $inicio,
                    'fin' => $fin,
                    'tiempo_conexion' => $tiempo_conexion,
                ];
                $total_conexion += $tiempo_conexion;

                // Iniciar nuevo lapso
                $inicio = $actual;
                $fin = $actual;
                $tiempo_conexion = 0;
            }
        }

        // Agregar último lapso
        $lapsos[] = [
            'inicio' => $inicio,
            'fin' => $fin,
            'tiempo_conexion' => $tiempo_conexion,
        ];
        $total_conexion += $tiempo_conexion;

        return [
            'lapsos' => $lapsos,
            'total_conexion' => $total_conexion
        ];
    }
}
