<?php

namespace App\Livewire;

use App\Models\ChatContactoCanal;
use App\Models\ChatWhatsappChannel;
use App\Models\Empleado;
use App\Models\Historial;
use App\Models\Soporte;
use App\Models\Tarea;
use App\Models\ViewUsuarioActivo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class TareasBoard extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $tab                     = 'pool';
    public string $filtroTipo              = '';
    public string $filtroEmpleadoAsignadas = '';

    // ── Modal tarea manual ───────────────────────────────────────────────────
    public bool   $showFormModal = false;
    public string $formNombre    = '';
    public string $formDesc      = '';
    public string $formPrioridad = 'media';
    public string $formFecha     = '';

    // ── Modal tomar masivo ───────────────────────────────────────────────────
    public int   $masivoAssigneeId = 0;
    public array $masivos = [
        'cobrar_usuario'    => 0,
        'quitar_usuario'    => 0,
        'renovar_cuenta'    => 0,
        'cuenta_caida'      => 0,
        'colapso_cuenta'    => 0,
        'soporte_pendiente' => 0,
        'agregar_stock'     => 0,
    ];

    // ── WhatsApp ─────────────────────────────────────────────────────────────
    public string $plantillaCobro      = '';
    public bool   $showPlantillaEditor = false;

    const PLANTILLA_DEFAULT = "Hola {nombre}, te recordamos que tu membresía vence el {fecha}. Por favor renueva para mantener el servicio. ¡Gracias! 🙏";

    protected function rules(): array
    {
        return [
            'formNombre'     => 'required|string|max:255',
            'formDesc'       => 'nullable|string',
            'formPrioridad'  => 'required|in:alta,media,baja',
            'formFecha'      => 'nullable|date',
            'plantillaCobro' => 'nullable|string|max:1000',
        ];
    }

    public function mount(): void
    {
        $this->plantillaCobro = $this->user()->plantilla_cobro ?? '';
    }

    // ─── Tabs ─────────────────────────────────────────────────────────────────

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function updatingFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEmpleadoAsignadas(): void
    {
        $this->resetPage();
    }

    // ─── Tomar / liberar (individual) ────────────────────────────────────────

    public function tomar(int $id): void
    {
        $empId = $this->user()->idemp;
        $tarea = Tarea::where('id', $id)->whereNull('assignee_id')->where('completada', false)->first();

        if (! $tarea) {
            $this->dispatch('notify', type: 'warning', msg: 'Esta tarea ya fue tomada por otro empleado.');
            return;
        }

        $tarea->update([
            'assignee_id'  => $empId,
            'asignado_por' => $empId,
            'assigned_at'  => now(),
        ]);

        $this->registrarHistorial('Tarea tomada', $tarea);
        $this->tab = 'mis_tareas';
        $this->dispatch('notify', type: 'success', msg: "'{$tarea->nombretarea}' agregada a tus tareas.");
    }

    public function liberar(int $id): void
    {
        $empId = $this->user()->idemp;

        $query = Tarea::where('id', $id)->where('completada', false);
        $tarea = $this->user()->isAdmin()
            ? $query->first()
            : $query->where('assignee_id', $empId)->first();

        if (! $tarea) return;

        $tarea->update(['assignee_id' => null, 'asignado_por' => null, 'assigned_at' => null]);
        $this->registrarHistorial('Tarea devuelta al pool', $tarea);
        $this->dispatch('notify', type: 'info', msg: 'Tarea devuelta al pool.');
    }

    // ─── Asignar individual (admins) ──────────────────────────────────────────

    public function asignar(int $tareaId, int $empId): void
    {
        if (! $this->user()->isAdmin()) {
            $this->dispatch('notify', type: 'danger', msg: 'Sin permiso para asignar tareas.');
            return;
        }
        if ($empId <= 0) return;

        $tarea = Tarea::where('id', $tareaId)->where('completada', false)->first();
        if (! $tarea) return;

        $tarea->update([
            'assignee_id'  => $empId,
            'asignado_por' => $this->user()->idemp,
            'assigned_at'  => now(),
        ]);
        $this->registrarHistorial("Tarea asignada al empleado #{$empId}", $tarea);
        $this->dispatch('notify', type: 'success', msg: 'Tarea asignada.');
    }

    // ─── Reasignar (admin) ───────────────────────────────────────────────────

    public function reasignar(int $tareaId, int $nuevoEmpId): void
    {
        if (! $this->user()->isAdmin()) {
            $this->dispatch('notify', type: 'danger', msg: 'Sin permiso para reasignar.');
            return;
        }
        if ($nuevoEmpId <= 0) return;

        $tarea = Tarea::where('id', $tareaId)->where('completada', false)->first();
        if (! $tarea) return;

        $anterior = optional(Empleado::find($tarea->assignee_id))->nombreemp ?? "emp#{$tarea->assignee_id}";
        $nuevo    = optional(Empleado::find($nuevoEmpId))->nombreemp ?? "emp#{$nuevoEmpId}";

        $tarea->update([
            'assignee_id'  => $nuevoEmpId,
            'asignado_por' => $this->user()->idemp,
            'assigned_at'  => now(),
        ]);

        $this->registrarHistorial("Tarea reasignada: {$anterior} → {$nuevo}", $tarea);
        $this->dispatch('notify', type: 'success', msg: "Reasignada a {$nuevo}.");
    }

    // ─── Toma masiva (recibe valores desde Alpine.js) ────────────────────────

    public function tomarMasivo(array $masivos = [], int $assigneeId = 0): void
    {
        if (!empty($masivos)) {
            foreach (array_keys($this->masivos) as $k) {
                $this->masivos[$k] = isset($masivos[$k]) ? max(0, (int) $masivos[$k]) : 0;
            }
        }

        $empId    = $this->user()->idemp;
        $isAdmin  = $this->user()->isAdmin();
        // assigneeId viene directo desde Alpine para evitar race conditions con wire:model
        $targetId = ($isAdmin && $assigneeId > 0) ? $assigneeId : $empId;

        $total = 0;

        foreach ($this->masivos as $tipo => $cantidad) {
            $cantidad = max(0, (int) $cantidad);
            if ($cantidad === 0) continue;

            $ids = $this->getIdsAgrupados($tipo, $cantidad);
            if (empty($ids)) continue;

            $asignadas = Tarea::whereIn('id', $ids)
                ->whereNull('assignee_id')
                ->where('completada', false)
                ->update([
                    'assignee_id'  => $targetId,
                    'asignado_por' => $empId,
                    'assigned_at'  => now(),
                ]);

            $total += $asignadas;
        }

        $nombre = $targetId === $empId
            ? 'tus tareas'
            : (optional(Empleado::find($targetId))->nombreemp ?? 'el empleado');

        $this->masivos = array_fill_keys(array_keys($this->masivos), 0);

        $this->tab = ($targetId === $empId) ? 'mis_tareas' : 'asignadas';
        $this->js("window.dispatchEvent(new CustomEvent('close-modal', { detail: 'tareasMasivoModal' }))");

        $this->dispatch('notify',
            type: $total > 0 ? 'success' : 'warning',
            msg: $total > 0
                ? "{$total} tareas asignadas a {$nombre}."
                : 'No hay tareas disponibles para los tipos seleccionados.'
        );
    }

    // ─── Liberar todas (admin) ────────────────────────────────────────────────

    public function liberarTodas(): void
    {
        if (! $this->user()->isAdmin()) {
            $this->dispatch('notify', type: 'danger', msg: 'Sin permiso para liberar tareas.');
            return;
        }

        $query = Tarea::whereNotNull('assignee_id')->where('completada', false);

        if ($this->filtroEmpleadoAsignadas) {
            $query->where('assignee_id', $this->filtroEmpleadoAsignadas);
        }

        $count = (clone $query)->count();

        if ($count === 0) {
            $this->dispatch('notify', type: 'warning', msg: 'No hay tareas asignadas para devolver.');
            return;
        }

        $query->update(['assignee_id' => null, 'asignado_por' => null, 'assigned_at' => null]);

        $filtroDesc = $this->filtroEmpleadoAsignadas
            ? (optional(Empleado::find($this->filtroEmpleadoAsignadas))->nombreemp ?? "emp#{$this->filtroEmpleadoAsignadas}")
            : 'todos';

        Historial::create([
            'accion'      => "Liberación masiva: {$count} tareas devueltas al pool",
            'descripcion' => json_encode(['count' => $count, 'filtro_empleado' => $filtroDesc]),
            'empleado_id' => $this->user()->idemp,
            'created_at'  => now(),
        ]);

        $this->dispatch('notify', type: 'info', msg: "{$count} tarea(s) devuelta(s) al pool.");
    }

    // ─── Completar ────────────────────────────────────────────────────────────

    public function completar(int $id): void
    {
        $empId = $this->user()->idemp;
        $tarea = Tarea::where('id', $id)
            ->where(function ($q) use ($empId) {
                $q->where('assignee_id', $empId)->orWhereNull('assignee_id');
            })
            ->where('completada', false)
            ->first();

        if (! $tarea) {
            $this->dispatch('notify', type: 'warning', msg: 'No puedes completar esta tarea.');
            return;
        }

        $tarea->update([
            'completada'       => true,
            'completada_por'   => $empId,
            'fecha_completada' => now(),
        ]);
        $this->registrarHistorial('Tarea completada', $tarea);
        $this->dispatch('notify', type: 'success', msg: "'{$tarea->nombretarea}' completada. ✅");
    }

    // ─── Tarea manual ─────────────────────────────────────────────────────────

    public function guardarManual(): void
    {
        $this->validate([
            'formNombre'    => 'required|string|max:255',
            'formDesc'      => 'nullable|string',
            'formPrioridad' => 'required|in:alta,media,baja',
            'formFecha'     => 'nullable|date',
        ]);
        $empId = $this->user()->idemp;

        $tarea = Tarea::create([
            'tipo_tarea'   => 'manual',
            'nombretarea'  => $this->formNombre,
            'descripcion'  => $this->formDesc ?: null,
            'prioridad'    => $this->formPrioridad,
            'fechalimit'   => $this->formFecha ?: null,
            'assignee_id'  => $empId,
            'asignado_por' => $empId,
            'assigned_at'  => now(),
            'completada'   => false,
        ]);

        $this->registrarHistorial('Tarea manual creada', $tarea);
        $this->reset(['showFormModal', 'formNombre', 'formDesc', 'formPrioridad', 'formFecha']);
        $this->tab = 'mis_tareas';
        $this->js("window.dispatchEvent(new CustomEvent('close-modal',{detail:'tareaManualModal'}))");
        $this->dispatch('notify', type: 'success', msg: 'Tarea creada.');
    }

    // ─── Ayudar a un compañero (tomar tarea asignada a otro) ─────────────────

    public function ayudar(int $id): void
    {
        $empId = $this->user()->idemp;

        $tarea = Tarea::where('id', $id)
            ->whereNotNull('assignee_id')
            ->where('assignee_id', '!=', $empId)
            ->where('completada', false)
            ->first();

        if (! $tarea) {
            $this->dispatch('notify', type: 'warning', msg: 'Esta tarea ya no está disponible.');
            return;
        }

        $anterior = optional(Empleado::find($tarea->assignee_id))->nombreemp ?? "empleado #{$tarea->assignee_id}";

        $tarea->update([
            'assignee_id' => $empId,
            'assigned_at' => now(),
        ]);

        $this->registrarHistorial("Tarea tomada para ayudar (antes: {$anterior})", $tarea);
        $this->tab = 'mis_tareas';
        $this->dispatch('notify', type: 'success', msg: "'{$tarea->nombretarea}' tomada de {$anterior}. ¡Gracias por ayudar!");
    }

    public function eliminar(int $id): void
    {
        if (! $this->user()->hasPermissionTo('tareas.destroy')) {
            $this->dispatch('notify', type: 'danger', msg: 'Sin permiso para eliminar.');
            return;
        }
        Tarea::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'info', msg: 'Tarea eliminada.');
    }

    // ─── WhatsApp: plantilla ──────────────────────────────────────────────────

    public function guardarPlantilla(): void
    {
        $this->validate(['plantillaCobro' => 'nullable|string|max:1000']);
        $this->user()->update(['plantilla_cobro' => $this->plantillaCobro ?: null]);
        $this->showPlantillaEditor = false;
        $this->dispatch('notify', type: 'success', msg: 'Plantilla guardada.');
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $empId   = $this->user()->idemp;
        $isAdmin = $this->user()->isAdmin();

        $poolQuery = Tarea::disponibles()
            ->when($this->filtroTipo, fn($q) => $q->where('tipo_tarea', $this->filtroTipo))
            ->orderByRaw("CASE WHEN prioridad='alta' THEN 1 WHEN prioridad='media' THEN 2 ELSE 3 END")
            ->orderBy('fechalimit');

        $misQuery = Tarea::asignadasA($empId)
            ->when($this->filtroTipo, fn($q) => $q->where('tipo_tarea', $this->filtroTipo))
            ->orderByRaw("CASE WHEN prioridad='alta' THEN 1 WHEN prioridad='media' THEN 2 ELSE 3 END")
            ->orderBy('fechalimit');

        $totalPool      = (clone $poolQuery)->count();
        $totalMias      = (clone $misQuery)->count();
        $completadasHoy = Tarea::where('completada_por', $empId)->where('completada', true)
            ->whereDate('fecha_completada', today())->count();

        $pool      = $this->tab === 'pool'
            ? $poolQuery->paginate(15)
            : collect();
        $misTareas = $this->tab === 'mis_tareas'
            ? $misQuery->get()
            : collect();
        $completadas = $this->tab === 'completadas'
            ? Tarea::where('completada_por', $empId)->where('completada', true)
                ->orderBy('fecha_completada', 'desc')->paginate(20)
            : collect();

        $asignadasQuery = $isAdmin
            ? Tarea::whereNotNull('assignee_id')
                ->where('completada', false)
                ->with('assignee')
                ->when($this->filtroTipo, fn($q) => $q->where('tipo_tarea', $this->filtroTipo))
                ->when($this->filtroEmpleadoAsignadas, fn($q) => $q->where('assignee_id', $this->filtroEmpleadoAsignadas))
                ->orderByRaw("CASE WHEN prioridad='alta' THEN 1 WHEN prioridad='media' THEN 2 ELSE 3 END")
                ->orderBy('assignee_id')
            : null;

        $totalAsignadas = $isAdmin ? (clone $asignadasQuery)->count() : 0;
        $todasAsignadas = ($isAdmin && $this->tab === 'asignadas')
            ? $asignadasQuery->paginate(20)
            : collect();

        $disponiblesPorTipo = Tarea::disponibles()
            ->select('tipo_tarea', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo_tarea')
            ->pluck('total', 'tipo_tarea')
            ->toArray();

        $empleados = $isAdmin ? Empleado::orderBy('nombreemp')->whereHas('roles')->get(['idemp', 'nombreemp']) : collect();
        $tipos     = Tarea::TIPOS;

        $tieneCanalWsp = ChatWhatsappChannel::availableForOutbound()->exists();

        $datosWspPorTarea = ($this->tab === 'mis_tareas' && $tieneCanalWsp)
            ? $this->resolverDatosWspBatch()
            : [];

        $contextoPorTarea = [];
        if ($this->tab === 'pool' && $pool->isNotEmpty()) {
            $contextoPorTarea = $this->resolverContextoPorTareas($pool->getCollection());
        } elseif ($this->tab === 'mis_tareas' && $misTareas->isNotEmpty()) {
            $contextoPorTarea = $this->resolverContextoPorTareas($misTareas);
        }

        // Tareas de compañeros: solo cuando el pool está vacío y el empleado puede ayudar
        $tareasDeOtros = ($this->tab === 'pool' && $totalPool === 0)
            ? Tarea::whereNotNull('assignee_id')
                ->where('assignee_id', '!=', $empId)
                ->where('completada', false)
                ->when($this->filtroTipo, fn($q) => $q->where('tipo_tarea', $this->filtroTipo))
                ->orderByRaw("CASE WHEN prioridad='alta' THEN 1 WHEN prioridad='media' THEN 2 ELSE 3 END")
                ->with('assignee:idemp,nombreemp')
                ->limit(20)
                ->get()
            : collect();

        return view('livewire.tareas-board', compact(
            'pool', 'misTareas', 'completadas', 'todasAsignadas',
            'totalPool', 'totalMias', 'completadasHoy', 'totalAsignadas',
            'empleados', 'isAdmin', 'tipos', 'disponiblesPorTipo',
            'tieneCanalWsp', 'datosWspPorTarea', 'tareasDeOtros', 'contextoPorTarea'
        ));
    }

    // ─── IDs agrupados por criterio óptimo ───────────────────────────────────

    private function getIdsAgrupados(string $tipo, int $limit): array
    {
        $base = Tarea::disponibles()->where('tipo_tarea', $tipo);

        return match($tipo) {

            'cobrar_usuario' => (clone $base)
                ->leftJoin('view_usuarios_activos as vu', 'vu.iddet', '=', 'tareas.related_id')
                ->orderBy('vu.fecha_vencimiento')
                ->orderBy('vu.idcue')
                ->select('tareas.id')
                ->limit($limit)
                ->pluck('tareas.id')
                ->toArray(),

            'quitar_usuario' => (clone $base)
                ->leftJoin('view_usuarios_activos as vu', 'vu.iddet', '=', 'tareas.related_id')
                ->orderBy('vu.idcue')
                ->orderBy('vu.fecha_vencimiento')
                ->select('tareas.id')
                ->limit($limit)
                ->pluck('tareas.id')
                ->toArray(),

            'renovar_cuenta', 'cuenta_caida', 'colapso_cuenta' => (clone $base)
                ->leftJoin('cuentas as cu', 'cu.idcue', '=', 'tareas.related_id')
                ->leftJoin('valores as va', 'va.idval', '=', 'cu.idval')
                ->orderBy('va.idser')
                ->orderBy('tareas.related_id')
                ->select('tareas.id')
                ->limit($limit)
                ->pluck('tareas.id')
                ->toArray(),

            'soporte_pendiente' => (clone $base)
                ->leftJoin('soportes as so', DB::raw('CAST(so.idsop AS CHAR)'), '=', 'tareas.related_id')
                ->leftJoin('cuentas as cu', 'cu.idcue', '=', 'so.idcue')
                ->leftJoin('valores as va', 'va.idval', '=', 'cu.idval')
                ->orderBy('va.idser')
                ->orderBy('so.idcue')
                ->select('tareas.id')
                ->limit($limit)
                ->pluck('tareas.id')
                ->toArray(),

            default => (clone $base)
                ->orderByRaw("CASE WHEN prioridad='alta' THEN 1 WHEN prioridad='media' THEN 2 ELSE 3 END")
                ->select('id')
                ->limit($limit)
                ->pluck('id')
                ->toArray(),
        };
    }

    // ─── Contexto enriquecido por tarea (cliente, idcli, tipo soporte) ──────────

    private function resolverContextoPorTareas(\Illuminate\Support\Collection $tareas): array
    {
        $context = [];

        // soporte_pendiente → Soporte → Cliente
        $idSops = $tareas->where('tipo_tarea', 'soporte_pendiente')
            ->pluck('related_id')->filter()->map(fn($v) => (int) $v)->toArray();

        if (!empty($idSops)) {
            $soportes = Soporte::whereIn('idsop', $idSops)
                ->with('cliente:idcli,nombrecli')
                ->get(['idsop', 'idcli', 'idcue', 'tipo']);

            foreach ($tareas->where('tipo_tarea', 'soporte_pendiente') as $t) {
                $s = $soportes->firstWhere('idsop', (int) $t->related_id);
                if ($s) {
                    $context[$t->id] = [
                        'tipo_tarea' => 'soporte_pendiente',
                        'idcli'      => $s->idcli,
                        'cliente'    => $s->cliente?->nombrecli ?? 'Cliente #' . $s->idcli,
                        'idcue'      => $s->idcue,
                        'tipo'       => $s->tipo,
                        'idsop'      => $s->idsop,
                    ];
                }
            }
        }

        // cobrar_usuario / quitar_usuario → ViewUsuarioActivo → idcli
        $idDets = $tareas->whereIn('tipo_tarea', ['cobrar_usuario', 'quitar_usuario'])
            ->pluck('related_id')->filter()->map(fn($v) => (int) $v)->toArray();

        if (!empty($idDets)) {
            $usuarios = ViewUsuarioActivo::whereIn('iddet', $idDets)
                ->get(['iddet', 'idcli', 'nombre_cliente', 'idcue']);

            foreach ($tareas->whereIn('tipo_tarea', ['cobrar_usuario', 'quitar_usuario']) as $t) {
                $u = $usuarios->firstWhere('iddet', (int) $t->related_id);
                if ($u) {
                    $context[$t->id] = [
                        'tipo_tarea' => $t->tipo_tarea,
                        'idcli'      => $u->idcli,
                        'cliente'    => $u->nombre_cliente ?? 'Cliente',
                        'idcue'      => $u->idcue,
                    ];
                }
            }
        }

        return $context;
    }

    // ─── WhatsApp: pre-computar datos para envío JS ───────────────────────────

    private function resolverDatosWspBatch(): array
    {
        $empId  = $this->user()->idemp;
        $tareas = Tarea::where('assignee_id', $empId)
            ->where('tipo_tarea', 'cobrar_usuario')
            ->where('completada', false)
            ->get(['id', 'related_id']);

        if ($tareas->isEmpty()) return [];

        $idDets = $tareas->pluck('related_id')
            ->filter()
            ->map(fn($v) => (int) $v)
            ->toArray();

        $usuarios = ViewUsuarioActivo::whereIn('iddet', $idDets)
            ->with('cliente')
            ->get()
            ->keyBy('iddet');

        $idClis = $usuarios->pluck('idcli')->filter()->unique()->toArray();

        $contactos = empty($idClis) ? collect() : ChatContactoCanal::whereIn('idcli', $idClis)
            ->where('canal', 'whatsapp')
            ->whereNotNull('telefono')
            ->orderByDesc('last_seen_at')
            ->get(['idcli', 'telefono'])
            ->groupBy('idcli')
            ->map(fn($g) => $g->first()->telefono);

        $template = trim($this->user()->plantilla_cobro ?? '') ?: self::PLANTILLA_DEFAULT;
        $result   = [];

        foreach ($tareas as $tarea) {
            $iddet   = (int) $tarea->related_id;
            $usuario = $usuarios->get($iddet);
            $idcli   = $usuario?->idcli;

            $nombre = $usuario?->nombre_cliente ?? 'Cliente';
            $fecha  = $usuario?->fecha_vencimiento
                ? \Carbon\Carbon::parse($usuario->fecha_vencimiento)->format('d/m/Y')
                : 'próximamente';

            $telefono = $idcli
                ? (string) ($contactos->get($idcli) ?? $usuario?->cliente?->telefonocli ?? '')
                : '';

            $result[$tarea->id] = [
                'nombre'   => $nombre,
                'telefono' => $telefono,
                'idcli'    => (int) ($idcli ?? 0),
                'mensaje'  => str_replace(['{nombre}', '{fecha}'], [$nombre, $fecha], $template),
            ];
        }

        return $result;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function user(): \App\Models\Empleado
    {
        /** @var \App\Models\Empleado */
        return Auth::user();
    }

    private function registrarHistorial(string $accion, Tarea $tarea): void
    {
        Historial::create([
            'accion'      => $accion,
            'descripcion' => json_encode(['tarea_id' => $tarea->id, 'nombre' => $tarea->nombretarea]),
            'empleado_id' => $this->user()->idemp,
            'created_at'  => now(),
        ]);
    }
}
