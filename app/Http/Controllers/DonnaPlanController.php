<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DonnaPlan;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DonnaPlanController extends Controller
{
    public function index()
    {
        if (!Gate::allows('donna.planes')) {
            abort(403, 'No tienes permiso para ver los planes de Donna.');
        }

        $planes = DonnaPlan::orderBy('sort_order')->orderBy('service_type')->get();

        return view('donna.planes.index', compact('planes'));
    }

    public function store(Request $request)
    {
        if (!Gate::allows('donna.planes.store')) {
            return $this->jsonOrAbort($request, 403, 'No tienes permiso para crear planes de Donna.');
        }

        $request->validate([
            'code'          => 'required|string|max:50|unique:donna_plans,code',
            'name'          => 'required|string|max:100',
            'service_type'  => 'required|in:personal,business',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|min:0',
            'currency'      => 'nullable|string|max:3',
            'billing_cycle' => 'required|in:monthly,yearly,one_time',
            'features'      => 'nullable|array',
            'features.*'    => 'nullable|string|max:200',
            'is_active'     => 'required|boolean',
            'sort_order'    => 'nullable|integer|min:0',
        ]);

        $features = collect($request->features ?? [])
            ->filter(fn($f) => !empty(trim($f)))
            ->values()
            ->all();

        $plan = DonnaPlan::create([
            'code'          => $request->code,
            'name'          => $request->name,
            'service_type'  => $request->service_type,
            'description'   => $request->description,
            'price'         => $request->price,
            'currency'      => $request->currency ?? 'USD',
            'billing_cycle' => $request->billing_cycle,
            'features_json' => !empty($features) ? $features : null,
            'is_active'     => $request->is_active,
            'sort_order'    => $request->sort_order ?? 0,
        ]);

        Historial::create([
            'accion'      => 'Creación de Plan Donna',
            'descripcion' => 'Plan: ' . $plan->name . ' | Precio: $' . $plan->price,
            'empleado_id' => Auth::user()->idemp,
            'created_at'  => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Plan creado exitosamente.', 'plan' => $plan]);
        }

        return redirect()->route('donna.planes.index')->with('success', 'Plan "' . $plan->name . '" creado exitosamente.');
    }

    public function show(Request $request, string $id)
    {
        if (!Gate::allows('donna.planes')) {
            return $this->jsonOrAbort($request, 403, 'Sin permiso.');
        }

        $plan = DonnaPlan::findOrFail($id);

        if ($request->expectsJson()) {
            return response()->json($plan);
        }

        abort(404);
    }

    public function update(Request $request, string $id)
    {
        if (!Gate::allows('donna.planes.store')) {
            return $this->jsonOrAbort($request, 403, 'No tienes permiso para editar planes de Donna.');
        }

        $plan = DonnaPlan::findOrFail($id);

        $request->validate([
            'code'          => 'required|string|max:50|unique:donna_plans,code,' . $id,
            'name'          => 'required|string|max:100',
            'service_type'  => 'required|in:personal,business',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|min:0',
            'currency'      => 'nullable|string|max:3',
            'billing_cycle' => 'required|in:monthly,yearly,one_time',
            'features'      => 'nullable|array',
            'features.*'    => 'nullable|string|max:200',
            'is_active'     => 'required|boolean',
            'sort_order'    => 'nullable|integer|min:0',
        ]);

        $features = collect($request->features ?? [])
            ->filter(fn($f) => !empty(trim($f)))
            ->values()
            ->all();

        $plan->update([
            'code'          => $request->code,
            'name'          => $request->name,
            'service_type'  => $request->service_type,
            'description'   => $request->description,
            'price'         => $request->price,
            'currency'      => $request->currency ?? 'USD',
            'billing_cycle' => $request->billing_cycle,
            'features_json' => !empty($features) ? $features : null,
            'is_active'     => $request->is_active,
            'sort_order'    => $request->sort_order ?? 0,
        ]);

        Historial::create([
            'accion'      => 'Edición de Plan Donna',
            'descripcion' => 'Plan: ' . $plan->name . ' | Precio: $' . $plan->price,
            'empleado_id' => Auth::user()->idemp,
            'created_at'  => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Plan actualizado exitosamente.', 'plan' => $plan]);
        }

        return redirect()->route('donna.planes.index')->with('success', 'Plan "' . $plan->name . '" actualizado.');
    }

    public function destroy(Request $request, string $id)
    {
        if (!Gate::allows('donna.planes.destroy')) {
            return $this->jsonOrAbort($request, 403, 'No tienes permiso para eliminar planes de Donna.');
        }

        $plan = DonnaPlan::findOrFail($id);
        $nombre = $plan->name;
        $plan->delete();

        Historial::create([
            'accion'      => 'Eliminación de Plan Donna',
            'descripcion' => 'Plan eliminado: ' . $nombre,
            'empleado_id' => Auth::user()->idemp,
            'created_at'  => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Plan eliminado.']);
        }

        return redirect()->route('donna.planes.index')->with('success', 'Plan "' . $nombre . '" eliminado.');
    }

    private function jsonOrAbort(Request $request, int $code, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], $code);
        }
        abort($code, $message);
    }
}
