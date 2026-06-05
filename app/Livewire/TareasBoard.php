<?php

namespace App\Livewire;

use App\Models\ChatContactoCanal;
use App\Models\ChatWhatsappChannel;
use App\Models\Empleado;
use App\Models\Historial;
use App\Models\Tarea;
use App\Models\ViewUsuarioActivo;
use App\Services\Chat\WhatsAppOutboundService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class TareasBoard extends Component
{
    use WithPagination;

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
    ];

    // ── WhatsApp ─────────────────────────────────────────────────────────────
    public string $plantillaCobro    = '';
    public bool   $showPlantillaEditor = false;
    public array  $selectedTareaIds  = [];

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
        $this->selectedTareaIds = [];
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
            $this->dispatch('notify', ['type' => 'warning', 'msg' => 'Esta tarea ya fue tomada por otro empleado.']);
            return;
        }

        $tarea->update([
            'assignee_id'  => $empId,
            'asignado_por' => $empId,
            'assigned_at'  => now(),
        ]);

        $this->registrarHistorial('Tarea tomada', $tarea);
        $this->tab = 'mis_tareas';
        $this->dispatch('notify', ['type' => 'success', 'msg' => "'{$tarea->nombretarea}' agregada a tus tareas."]);
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
        $this->dispatch('notify', ['type' => 'info', 'msg' => 'Tarea devuelta al pool.']);
    }

    // ─── Asignar individual (admins) ──────────────────────────────────────────

    public function asignar(int $tareaId, int $empId): void
    {
        if (! $this->user()->isAdmin()) {
            $this->dispatch('notify', ['type' => 'danger', 'msg' => 'Sin permiso para asignar tareas.']);
            return;
        }
        $tarea = Tarea::where('id', $tareaId)->where('completada', false)->first();
        if (! $tarea) return;

        $tarea->update([
            'assignee_id'  => $empId,
            'asignado_por' => $this->user()->idemp,
            'assigned_at'  => now(),
        ]);
        $this->registrarHistorial("Tarea asignada al empleado #{$empId}", $tarea);
        $this->dispatch('notify', ['type' => 'success', 'msg' => 'Tarea asignada.']);
    }

    // ─── Reasignar (admin) ───────────────────────────────────────────────────

    public function reasignar(int $tareaId, int $nuevoEmpId): void
    {
        if (! $this->user()->isAdmin()) {
            $this->dispatch('notify', ['type' => 'danger', 'msg' => 'Sin permiso para reasignar.']);
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
        $this->dispatch('notify', ['type' => 'success', 'msg' => "Reasignada a {$nuevo}."]);
    }

    // ─── Toma masiva (recibe valores desde Alpine.js) ────────────────────────

    public function tomarMasivo(array $masivos = []): void
    {
        if (!empty($masivos)) {
            foreach (array_keys($this->masivos) as $k) {
                $this->masivos[$k] = isset($masivos[$k]) ? max(0, (int) $masivos[$k]) : 0;
            }
        }

        $empId    = $this->user()->idemp;
        $isAdmin  = $this->user()->isAdmin();
        $targetId = ($isAdmin && $this->masivoAssigneeId > 0) ? $this->masivoAssigneeId : $empId;

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

        $this->masivos          = array_fill_keys(array_keys($this->masivos), 0);
        $this->masivoAssigneeId = 0;

        $this->tab = ($targetId === $empId) ? 'mis_tareas' : 'pool';
        $this->js("window.dispatchEvent(new CustomEvent('close-modal', { detail: 'tareasMasivoModal' }))");

        $this->dispatch('notify', [
            'type' => $total > 0 ? 'success' : 'warning',
            'msg'  => $total > 0
                ? "{$total} tareas asignadas a {$nombre}."
                : 'No hay tareas disponibles para los tipos seleccionados.',
        ]);
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
            $this->dispatch('notify', ['type' => 'warning', 'msg' => 'No puedes completar esta tarea.']);
            return;
        }

        $tarea->update([
            'completada'       => true,
            'completada_por'   => $empId,
            'fecha_completada' => now(),
        ]);
        $this->registrarHistorial('Tarea completada', $tarea);
        $this->dispatch('notify', ['type' => 'success', 'msg' => "'{$tarea->nombretarea}' completada. ✅"]);
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
        $this->dispatch('notify', ['type' => 'success', 'msg' => 'Tarea creada.']);
    }

    public function eliminar(int $id): void
    {
        if (! $this->user()->hasPermissionTo('tareas.destroy')) {
            $this->dispatch('notify', ['type' => 'danger', 'msg' => 'Sin permiso para eliminar.']);
            return;
        }
        Tarea::findOrFail($id)->delete();
        $this->dispatch('notify', ['type' => 'info', 'msg' => 'Tarea eliminada.']);
    }

    // ─── WhatsApp: plantilla ──────────────────────────────────────────────────

    public function guardarPlantilla(): void
    {
        $this->validate(['plantillaCobro' => 'nullable|string|max:1000']);
        $this->user()->update(['plantilla_cobro' => $this->plantillaCobro ?: null]);
        $this->showPlantillaEditor = false;
        $this->dispatch('notify', ['type' => 'success', 'msg' => 'Plantilla guardada.']);
    }

    // ─── WhatsApp: envío individual ───────────────────────────────────────────

    public function enviarWsp(int $tareaId): void
    {
        $tarea = Tarea::where('id', $tareaId)
            ->where('assignee_id', $this->user()->idemp)
            ->where('tipo_tarea', 'cobrar_usuario')
            ->where('completada', false)
            ->first();

        if (! $tarea) {
            $this->dispatch('notify', ['type' => 'warning', 'msg' => 'Tarea no válida para envío.']);
            return;
        }

        [$enviados, $sinTel, $errores] = $this->enviarWspParaIdDets(
            collect([(int) $tarea->related_id])
        );

        if ($sinTel > 0) {
            $this->dispatch('notify', ['type' => 'warning', 'msg' => 'Cliente sin teléfono registrado.']);
        } elseif ($errores > 0) {
            $this->dispatch('notify', ['type' => 'danger', 'msg' => 'Error al enviar el mensaje.']);
        } else {
            $this->dispatch('notify', ['type' => 'success', 'msg' => 'Mensaje enviado por WhatsApp ✅']);
        }
    }

    // ─── WhatsApp: envío masivo (todos) ───────────────────────────────────────

    public function enviarWspTodos(): void
    {
        $idDets = Tarea::where('assignee_id', $this->user()->idemp)
            ->where('tipo_tarea', 'cobrar_usuario')
            ->where('completada', false)
            ->pluck('related_id')
            ->filter()
            ->map(fn($v) => (int) $v)
            ->values();

        if ($idDets->isEmpty()) {
            $this->dispatch('notify', ['type' => 'warning', 'msg' => 'No tienes tareas de cobro asignadas.']);
            return;
        }

        [$enviados, $sinTel, $errores] = $this->enviarWspParaIdDets($idDets);
        $this->dispatch('notify', $this->buildBulkNotify($enviados, $sinTel, $errores));
    }

    // ─── WhatsApp: envío seleccionados (llamado desde Alpine) ────────────────

    public function enviarWspSeleccionados(array $tareaIds = []): void
    {
        if (empty($tareaIds)) {
            $this->dispatch('notify', ['type' => 'warning', 'msg' => 'No hay tareas seleccionadas.']);
            return;
        }

        $idDets = Tarea::whereIn('id', $tareaIds)
            ->where('assignee_id', $this->user()->idemp)
            ->where('tipo_tarea', 'cobrar_usuario')
            ->where('completada', false)
            ->pluck('related_id')
            ->filter()
            ->map(fn($v) => (int) $v)
            ->values();

        if ($idDets->isEmpty()) {
            $this->dispatch('notify', ['type' => 'warning', 'msg' => 'No hay tareas de cobro en la selección.']);
            return;
        }

        [$enviados, $sinTel, $errores] = $this->enviarWspParaIdDets($idDets);
        $this->selectedTareaIds = [];
        $this->dispatch('notify', $this->buildBulkNotify($enviados, $sinTel, $errores));
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
            ? $poolQuery->paginate(15, pageName: 'poolPage')
            : collect();
        $misTareas = $this->tab === 'mis_tareas'
            ? $misQuery->get()
            : collect();
        $completadas = $this->tab === 'completadas'
            ? Tarea::where('completada_por', $empId)->where('completada', true)
                ->orderBy('fecha_completada', 'desc')->paginate(20, pageName: 'compPage')
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
            ? $asignadasQuery->paginate(20, pageName: 'asigPage')
            : collect();

        $disponiblesPorTipo = Tarea::disponibles()
            ->select('tipo_tarea', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo_tarea')
            ->pluck('total', 'tipo_tarea')
            ->toArray();

        $empleados = $isAdmin ? Empleado::orderBy('nombreemp')->get(['idemp', 'nombreemp']) : collect();
        $tipos     = Tarea::TIPOS;

        $tieneCanalWsp = ChatWhatsappChannel::availableForOutbound()->exists();

        return view('livewire.tareas-board', compact(
            'pool', 'misTareas', 'completadas', 'todasAsignadas',
            'totalPool', 'totalMias', 'completadasHoy', 'totalAsignadas',
            'empleados', 'isAdmin', 'tipos', 'disponiblesPorTipo',
            'tieneCanalWsp'
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

    // ─── WhatsApp helpers ─────────────────────────────────────────────────────

    /**
     * Envía mensajes a una lista de iddet y devuelve [enviados, sinTelefono, errores].
     *
     * @param Collection<int> $idDets
     * @return array{int, int, int}
     */
    private function enviarWspParaIdDets(Collection $idDets): array
    {
        $canal = ChatWhatsappChannel::availableForOutbound()->first();
        if (! $canal) {
            return [0, 0, $idDets->count()];
        }

        $usuarios = ViewUsuarioActivo::whereIn('iddet', $idDets->toArray())
            ->with('cliente')
            ->get()
            ->keyBy('iddet');

        $service  = app(WhatsAppOutboundService::class);
        $enviados = $sinTel = $errores = 0;

        foreach ($idDets as $iddet) {
            $usuario = $usuarios->get($iddet);
            $phone   = $this->resolverTelefono($usuario);

            if (! $phone) {
                $sinTel++;
                continue;
            }

            $mensaje = $this->buildMensaje($usuario);
            $result  = $service->sendText($phone, $mensaje, $canal->instance_name, $canal->api_key, $canal->server_url);

            $result['ok'] ? $enviados++ : $errores++;
        }

        return [$enviados, $sinTel, $errores];
    }

    private function resolverTelefono(?ViewUsuarioActivo $usuario): ?string
    {
        if (! $usuario || ! $usuario->cliente) return null;

        $contacto = ChatContactoCanal::where('idcli', $usuario->idcli)
            ->where('canal', 'whatsapp')
            ->whereNotNull('telefono')
            ->orderByDesc('last_seen_at')
            ->value('telefono');

        return $contacto ?: ($usuario->cliente->telefonocli ?: null);
    }

    private function buildMensaje(?ViewUsuarioActivo $usuario): string
    {
        $template = trim($this->user()->plantilla_cobro ?? '') ?: self::PLANTILLA_DEFAULT;

        $nombre = $usuario?->nombre_cliente ?? 'Cliente';
        $fecha  = $usuario?->fecha_vencimiento
            ? \Carbon\Carbon::parse($usuario->fecha_vencimiento)->format('d/m/Y')
            : 'próximamente';

        return str_replace(['{nombre}', '{fecha}'], [$nombre, $fecha], $template);
    }

    private function buildBulkNotify(int $enviados, int $sinTel, int $errores): array
    {
        $partes = [];
        if ($enviados > 0) $partes[] = "{$enviados} enviados ✅";
        if ($sinTel  > 0) $partes[] = "{$sinTel} sin teléfono";
        if ($errores > 0) $partes[] = "{$errores} con error";

        $msg  = implode(', ', $partes) ?: 'Sin resultados';
        $type = $enviados > 0 ? ($errores > 0 ? 'warning' : 'success') : 'danger';

        return ['type' => $type, 'msg' => $msg];
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
