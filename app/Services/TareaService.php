<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Tarea;
use App\Models\Cuenta;
use App\Models\Soporte;
use App\Models\ViewUsuarioActivo;
use App\Services\CuentaService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TareaService
{
    protected CuentaService $cuentaService;

    public function __construct(CuentaService $cuentaService)
    {
        $this->cuentaService = $cuentaService;
    }

    // ─── Punto de entrada principal ──────────────────────────────────────────

    public function sincronizarTareas(): array
    {
        $usuarios = ViewUsuarioActivo::all();
        $cuentas  = Cuenta::with(['valor'])->where('activocue', true)->get();

        $creadas = 0;
        $limpiadas = 0;

        $creadas  += $this->sincronizarCobrarUsuarios($usuarios);
        $creadas  += $this->sincronizarQuitarUsuarios($usuarios);
        $creadas  += $this->sincronizarRenovarCuentas($cuentas);
        $creadas  += $this->sincronizarCuentasCaidas($cuentas);
        $creadas  += $this->sincronizarColapsosCuentas($cuentas);
        $creadas  += $this->sincronizarSoportesPendientes();
        $creadas  += $this->sincronizarStockServicios();
        $limpiadas = $this->limpiarTareasObsoletas();
        $this->limpiarTareasGeneralesAntiguas();

        return compact('creadas', 'limpiadas');
    }

    // ─── Cobrar usuarios ─────────────────────────────────────────────────────

    private function sincronizarCobrarUsuarios(Collection $usuarios): int
    {
        $lista = $this->cuentaService->obtenerUsuariosACobrar($usuarios);
        $creadas = 0;
        $limit = Carbon::today()->setHour(23)->setMinute(59);

        foreach ($lista as $u) {
            $existe = Tarea::where('tipo_tarea', 'cobrar_usuario')
                ->where('related_model', 'ViewUsuarioActivo')
                ->where('related_id', (string) $u->iddet)
                ->where('completada', false)
                ->exists();

            if (! $existe) {
                Tarea::create([
                    'tipo_tarea'    => 'cobrar_usuario',
                    'related_model' => 'ViewUsuarioActivo',
                    'related_id'    => (string) $u->iddet,
                    'nombretarea'   => "Cobrar: {$u->nombre_cliente}",
                    'descripcion'   => "Cliente: {$u->nombre_cliente} | Cuenta: {$u->idcue} | Perfil: {$u->perfil} | Vence: " . Carbon::parse($u->fecha_vencimiento)->format('d/m/Y'),
                    'prioridad'     => 'media',
                    'fechalimit'    => $limit,
                    'completada'    => false,
                ]);
                $creadas++;
            }
        }

        // Eliminar tareas del pool para usuarios que ya no están en la lista
        $idsValidos = $lista->pluck('iddet')->map(fn($id) => (string) $id)->toArray();
        Tarea::where('tipo_tarea', 'cobrar_usuario')
            ->where('completada', false)
            ->whereNull('assignee_id')
            ->when(count($idsValidos), fn($q) => $q->whereNotIn('related_id', $idsValidos))
            ->delete();

        return $creadas;
    }

    // ─── Quitar usuarios ─────────────────────────────────────────────────────

    private function sincronizarQuitarUsuarios(Collection $usuarios): int
    {
        $lista = $this->cuentaService->obtenerUsuariosAQuitar($usuarios);
        $creadas = 0;
        $limit = Carbon::today()->setHour(23)->setMinute(59);

        foreach ($lista as $u) {
            $existe = Tarea::where('tipo_tarea', 'quitar_usuario')
                ->where('related_model', 'ViewUsuarioActivo')
                ->where('related_id', (string) $u->iddet)
                ->where('completada', false)
                ->exists();

            if (! $existe) {
                Tarea::create([
                    'tipo_tarea'    => 'quitar_usuario',
                    'related_model' => 'ViewUsuarioActivo',
                    'related_id'    => (string) $u->iddet,
                    'nombretarea'   => "Quitar: {$u->nombre_cliente}",
                    'descripcion'   => "Cliente: {$u->nombre_cliente} | Cuenta: {$u->idcue} | Perfil: {$u->perfil} | Venció: " . Carbon::parse($u->fecha_vencimiento)->format('d/m/Y'),
                    'prioridad'     => 'alta',
                    'fechalimit'    => $limit,
                    'completada'    => false,
                ]);
                $creadas++;
            }
        }

        $idsValidos = $lista->pluck('iddet')->map(fn($id) => (string) $id)->toArray();
        Tarea::where('tipo_tarea', 'quitar_usuario')
            ->where('completada', false)
            ->whereNull('assignee_id')
            ->when(count($idsValidos), fn($q) => $q->whereNotIn('related_id', $idsValidos))
            ->delete();

        return $creadas;
    }

    // ─── Renovar / eliminar cuentas ───────────────────────────────────────────

    private function sincronizarRenovarCuentas(Collection $cuentas): int
    {
        $lista = $this->cuentaService->obtenerCuentasPorVencer($cuentas);
        $creadas = 0;
        $limit = Carbon::today()->setHour(23)->setMinute(59);

        foreach ($lista as $c) {
            $existe = Tarea::where('tipo_tarea', 'renovar_cuenta')
                ->where('related_model', 'Cuenta')
                ->where('related_id', $c->idcue)
                ->where('completada', false)
                ->exists();

            if (! $existe) {
                Tarea::create([
                    'tipo_tarea'    => 'renovar_cuenta',
                    'related_model' => 'Cuenta',
                    'related_id'    => $c->idcue,
                    'nombretarea'   => "Renovar cuenta: {$c->idcue}",
                    'descripcion'   => "Cuenta: {$c->idcue} | Vence: " . Carbon::parse($c->fechavencue)->format('d/m/Y'),
                    'prioridad'     => 'alta',
                    'fechalimit'    => $limit,
                    'completada'    => false,
                ]);
                $creadas++;
            }
        }

        $idsValidos = $lista->pluck('idcue')->toArray();
        Tarea::where('tipo_tarea', 'renovar_cuenta')
            ->where('completada', false)
            ->whereNull('assignee_id')
            ->when(count($idsValidos), fn($q) => $q->whereNotIn('related_id', $idsValidos))
            ->delete();

        return $creadas;
    }

    // ─── Cuentas caídas ───────────────────────────────────────────────────────

    private function sincronizarCuentasCaidas(Collection $cuentas): int
    {
        $lista = $this->cuentaService->obtenerCuentasCaidas($cuentas);
        $creadas = 0;
        $limit = Carbon::today()->setHour(23)->setMinute(59);

        foreach ($lista as $c) {
            $existe = Tarea::where('tipo_tarea', 'cuenta_caida')
                ->where('related_model', 'Cuenta')
                ->where('related_id', $c->idcue)
                ->where('completada', false)
                ->exists();

            if (! $existe) {
                Tarea::create([
                    'tipo_tarea'    => 'cuenta_caida',
                    'related_model' => 'Cuenta',
                    'related_id'    => $c->idcue,
                    'nombretarea'   => "Cuenta caída: {$c->idcue}",
                    'descripcion'   => "La cuenta {$c->idcue} está marcada como caída. Requiere revisión.",
                    'prioridad'     => 'alta',
                    'fechalimit'    => $limit,
                    'completada'    => false,
                ]);
                $creadas++;
            }
        }

        $idsValidos = $lista->pluck('idcue')->toArray();
        Tarea::where('tipo_tarea', 'cuenta_caida')
            ->where('completada', false)
            ->whereNull('assignee_id')
            ->when(count($idsValidos), fn($q) => $q->whereNotIn('related_id', $idsValidos))
            ->delete();

        return $creadas;
    }

    // ─── Colapso de cuentas ───────────────────────────────────────────────────

    private function sincronizarColapsosCuentas(Collection $cuentas): int
    {
        $lista = $this->cuentaService->obtenerCuentasColapsadas($cuentas);
        $creadas = 0;
        $limit = Carbon::today()->setHour(23)->setMinute(59);

        foreach ($lista as $c) {
            $existe = Tarea::where('tipo_tarea', 'colapso_cuenta')
                ->where('related_model', 'Cuenta')
                ->where('related_id', $c->idcue)
                ->where('completada', false)
                ->exists();

            if (! $existe) {
                Tarea::create([
                    'tipo_tarea'    => 'colapso_cuenta',
                    'related_model' => 'Cuenta',
                    'related_id'    => $c->idcue,
                    'nombretarea'   => "Espacios saturados: {$c->idcue}",
                    'descripcion'   => "Cuenta {$c->idcue} superó el máximo de usuarios permitido ({$c->valor->pantmaxval}). Ajustar espacios.",
                    'prioridad'     => 'baja',
                    'fechalimit'    => $limit,
                    'completada'    => false,
                ]);
                $creadas++;
            }
        }

        $idsValidos = $lista->pluck('idcue')->toArray();
        Tarea::where('tipo_tarea', 'colapso_cuenta')
            ->where('completada', false)
            ->whereNull('assignee_id')
            ->when(count($idsValidos), fn($q) => $q->whereNotIn('related_id', $idsValidos))
            ->delete();

        return $creadas;
    }

    // ─── Soportes pendientes ─────────────────────────────────────────────────

    private function sincronizarSoportesPendientes(): int
    {
        if (! class_exists(Soporte::class)) return 0;

        $lista = Soporte::where('estado', 'pendiente')->get();
        $creadas = 0;
        $limit = Carbon::today()->setHour(23)->setMinute(59);

        foreach ($lista as $s) {
            $existe = Tarea::where('tipo_tarea', 'soporte_pendiente')
                ->where('related_model', 'Soporte')
                ->where('related_id', (string) $s->idsop)
                ->where('completada', false)
                ->exists();

            if (! $existe) {
                Tarea::create([
                    'tipo_tarea'    => 'soporte_pendiente',
                    'related_model' => 'Soporte',
                    'related_id'    => (string) $s->idsop,
                    'nombretarea'   => "Soporte #{$s->idsop}: {$s->tipo}",
                    'descripcion'   => "Cuenta: {$s->idcue} | Tipo: {$s->tipo} | " . Str::limit($s->descripcion, 120),
                    'prioridad'     => 'media',
                    'fechalimit'    => $limit,
                    'completada'    => false,
                ]);
                $creadas++;
            }
        }

        $idsValidos = $lista->pluck('idsop')->map(fn($id) => (string) $id)->toArray();
        Tarea::where('tipo_tarea', 'soporte_pendiente')
            ->where('completada', false)
            ->whereNull('assignee_id')
            ->when(count($idsValidos), fn($q) => $q->whereNotIn('related_id', $idsValidos))
            ->delete();

        return $creadas;
    }

    // ─── Stock bajo por servicio ─────────────────────────────────────────────

    private function sincronizarStockServicios(): int
    {
        $umbral = 3;

        // Por servicio: capacidad total (SUM pantmaxval de cuentas activas) menos usuarios activos
        $servicios = DB::table('servicios as s')
            ->join('valores as v', function ($j) {
                $j->on('v.idser', '=', 's.idser')->where('v.activoval', true);
            })
            ->join('cuentas as c', function ($j) {
                $j->on('c.idval', '=', 'v.idval')->where('c.activocue', true);
            })
            ->leftJoin('view_usuarios_activos as vua', function ($j) {
                $j->on('vua.idcue', '=', 'c.idcue')
                  ->whereRaw('vua.fecha_vencimiento > NOW()');
            })
            ->groupBy('s.idser', 's.nombreser')
            ->select([
                's.idser',
                's.nombreser',
                DB::raw('CAST(SUM(v.pantmaxval) AS SIGNED) - COUNT(vua.iddet) AS disponibles'),
            ])
            ->havingRaw('CAST(SUM(v.pantmaxval) AS SIGNED) - COUNT(vua.iddet) < ?', [$umbral])
            ->get();

        $creadas = 0;

        foreach ($servicios as $servicio) {
            $existe = Tarea::where('tipo_tarea', 'agregar_stock')
                ->where('related_model', 'Servicio')
                ->where('related_id', $servicio->idser)
                ->where('completada', false)
                ->exists();

            if (! $existe) {
                $disp = max(0, (int) $servicio->disponibles);
                Tarea::create([
                    'tipo_tarea'    => 'agregar_stock',
                    'related_model' => 'Servicio',
                    'related_id'    => $servicio->idser,
                    'nombretarea'   => "Stock bajo: {$servicio->nombreser}",
                    'descripcion'   => "Solo {$disp} espacio(s) disponible(s) para {$servicio->nombreser}. Comprar nuevas cuentas.",
                    'prioridad'     => $disp <= 0 ? 'alta' : 'media',
                    'fechalimit'    => Carbon::today()->setHour(23)->setMinute(59),
                    'completada'    => false,
                ]);
                $creadas++;
            }
        }

        // Limpiar tareas de servicios que ya recuperaron stock
        $idsConPocoStock = collect($servicios)->pluck('idser')->toArray();
        Tarea::where('tipo_tarea', 'agregar_stock')
            ->where('completada', false)
            ->whereNull('assignee_id')
            ->when(!empty($idsConPocoStock), fn($q) => $q->whereNotIn('related_id', $idsConPocoStock))
            ->delete();

        return $creadas;
    }

    // ─── Limpieza ─────────────────────────────────────────────────────────────

    private function limpiarTareasObsoletas(): int
    {
        // Tareas completadas hace más de 7 días
        return Tarea::where('completada', true)
            ->where('fecha_completada', '<', now()->subDays(7))
            ->delete();
    }

    private function limpiarTareasGeneralesAntiguas(): void
    {
        // Elimina las 5 tareas generales del sistema anterior
        $nombres = [
            'Quitar Usuarios',
            'Cobrar Usuarios',
            'Renovar o Eliminar Cuentas',
            'Arreglar Cuentas Caídas',
            'Ajustar Espacios de Clientes',
        ];
        Tarea::whereIn('nombretarea', $nombres)
            ->whereNull('tipo_tarea')
            ->delete();
    }
}
