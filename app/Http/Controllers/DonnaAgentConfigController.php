<?php

namespace App\Http\Controllers;

use App\Models\DonnaAgentConfig;
use App\Models\DonnaSubscription;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DonnaAgentConfigController extends Controller
{
    public function edit(string $id)
    {
        if (!Gate::allows('donna.suscripciones')) {
            abort(403);
        }

        $sub = DonnaSubscription::with(['plan', 'cliente'])->findOrFail($id);

        $config = DonnaAgentConfig::firstOrNew([
            'client_id'    => $sub->client_id,
            'service_type' => $sub->service_type,
        ]);

        $wh = $config->working_hours_json ?? [];

        return view('donna.configuraciones.edit', compact('sub', 'config', 'wh'));
    }

    public function update(Request $request, string $id)
    {
        if (!Gate::allows('donna.suscripciones.store')) {
            return $this->jsonOrAbort($request, 403, 'Sin permiso para editar la configuración.');
        }

        $sub = DonnaSubscription::findOrFail($id);

        // El navegador normaliza los saltos de línea de un <textarea> a CRLF al armar
        // el body de la petición, pero el atributo maxlength (y cualquier contador en
        // el front) cuenta .value.length, donde \n es 1 carácter. Igualamos a LF antes
        // de validar para que el límite max: de cada campo sea consistente con lo que
        // el usuario ve escrito en pantalla.
        $multilineFields = [
            'personal_context', 'business_description', 'business_context', 'business_logic',
            'sales_rules', 'support_rules', 'main_prompt', 'fallback_prompt',
            'welcome_message', 'out_of_hours_message', 'human_handoff_msg',
        ];
        $request->merge(array_combine(
            $multilineFields,
            array_map(
                fn ($field) => str_replace(["\r\n", "\r"], "\n", (string) $request->input($field)),
                $multilineFields
            )
        ));

        $rules = [
            'agent_name'            => 'nullable|string|max:50',
            'business_name'         => 'nullable|string|max:150',
            'tone'                  => 'nullable|string|max:30',
            'language'              => 'nullable|string|max:5',
            'timezone'              => 'nullable|string|max:50',
            'calendar_id'           => 'nullable|string|max:150',
            'wh_start'              => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'wh_end'                => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'wh_lunch'              => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'is_active'             => 'nullable|boolean',
        ];

        if ($sub->service_type === 'personal') {
            $rules['personal_context'] = 'nullable|string|max:3000';
        } else {
            $rules['business_description']  = 'nullable|string|max:3000';
            $rules['business_context']      = 'nullable|string|max:5000';
            $rules['business_logic']        = 'nullable|string|max:5000';
            $rules['sales_rules']           = 'nullable|string|max:5000';
            $rules['support_rules']         = 'nullable|string|max:5000';
            $rules['main_prompt']           = 'nullable|string|max:8000';
            $rules['fallback_prompt']       = 'nullable|string|max:2000';
            $rules['welcome_message']       = 'nullable|string|max:1000';
            $rules['out_of_hours_message']  = 'nullable|string|max:1000';
            $rules['human_handoff_msg']     = 'nullable|string|max:1000';
            $rules['knowledge_enabled']     = 'nullable|boolean';
            $rules['calendar_enabled']      = 'nullable|boolean';
            $rules['sheets_enabled']        = 'nullable|boolean';
            $rules['human_takeover_enabled']= 'nullable|boolean';
            $rules['max_tool_calls']        = 'nullable|integer|min:1|max:20';
            $rules['wait_seconds']          = 'nullable|integer|min:3|max:60';
            $rules['response_style']        = 'nullable|in:concise,moderate,detailed';
        }

        $request->validate($rules);

        $wh = array_filter([
            'start' => $request->input('wh_start') ?: null,
            'end'   => $request->input('wh_end') ?: null,
            'lunch' => $request->input('wh_lunch') ?: null,
        ]);

        $data = [
            'subscription_id' => $sub->id,
            'service_type'    => $sub->service_type,
            'agent_name'      => $request->input('agent_name') ?: 'Donna',
            'business_name'   => $request->input('business_name'),
            'tone'            => $request->input('tone') ?: 'profesional, amable y directa',
            'language'        => $request->input('language') ?: 'es',
            'timezone'        => $request->input('timezone') ?: 'America/Guayaquil',
            'calendar_id'     => $request->input('calendar_id') ?: 'primary',
            'working_hours_json' => $wh ?: null,
            'is_active'       => $request->boolean('is_active'),
        ];

        if ($sub->service_type === 'personal') {
            $data['personal_context'] = $request->input('personal_context');
        } else {
            $data['business_description']   = $request->input('business_description');
            $data['business_context']       = $request->input('business_context');
            $data['business_logic']         = $request->input('business_logic');
            $data['sales_rules']            = $request->input('sales_rules');
            $data['support_rules']          = $request->input('support_rules');
            $data['main_prompt']            = $request->input('main_prompt');
            $data['fallback_prompt']        = $request->input('fallback_prompt');
            $data['welcome_message']        = $request->input('welcome_message');
            $data['out_of_hours_message']   = $request->input('out_of_hours_message');
            $data['human_handoff_msg']      = $request->input('human_handoff_msg');
            $data['knowledge_enabled']      = $request->boolean('knowledge_enabled');
            $data['calendar_enabled']       = $request->boolean('calendar_enabled');
            $data['sheets_enabled']         = $request->boolean('sheets_enabled');
            $data['human_takeover_enabled'] = $request->boolean('human_takeover_enabled');
            $data['max_tool_calls']         = (int) ($request->input('max_tool_calls') ?: 8);
            $data['wait_seconds']           = (int) ($request->input('wait_seconds') ?: 35);
            $data['response_style']         = $request->input('response_style') ?: 'concise';
        }

        DonnaAgentConfig::updateOrCreate(
            ['client_id' => $sub->client_id, 'service_type' => $sub->service_type],
            $data
        );

        Historial::create([
            'accion'      => 'Configuración Donna actualizada (admin)',
            'descripcion' => 'Suscripción #' . $sub->id . ' | Cliente: ' . ($sub->cliente?->nombrecli ?? $sub->client_id),
            'empleado_id' => Auth::user()->idemp,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Configuración guardada exitosamente.']);
        }

        return redirect()->route('donna.suscripciones.config', $sub->id)
            ->with('success', 'Configuración guardada exitosamente.');
    }

    private function jsonOrAbort(Request $request, int $code, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], $code);
        }
        abort($code, $message);
    }
}
