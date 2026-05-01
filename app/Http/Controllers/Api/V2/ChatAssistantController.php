<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Support\ClienteAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatAssistantController extends Controller
{
    public function __construct()
    {
        request()->headers->set('Accept', 'application/json');
    }

    public function clientePorTelefono(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telefono' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $telefono = ClienteAuth::normalizePhone($request->input('telefono'));
        $cliente = Cliente::buscarPorTelefonoNormalizado($telefono);

        if (!$cliente) {
            return response()->json([
                'success' => true,
                'found' => false,
                'message' => 'Cliente no encontrado por telefono.',
                'data' => [
                    'telefono_consultado' => $telefono,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'found' => true,
            'message' => 'Cliente encontrado.',
            'data' => [
                'cliente' => [
                    'idcli' => $cliente->idcli,
                    'nombrecli' => $cliente->nombrecli,
                    'telefonocli' => $cliente->telefonocli,
                    'email' => $cliente->email,
                    'saldo' => (float) $cliente->saldo,
                ],
            ],
        ]);
    }

    public function crearCliente(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telefonocli' => 'required|string|max:50',
            'nombrecli' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $telefono = ClienteAuth::normalizePhone($request->input('telefonocli'));
        $clienteExistente = Cliente::buscarPorTelefonoNormalizado($telefono);

        if ($clienteExistente) {
            $updates = [];

            if ($request->filled('nombrecli') && empty($clienteExistente->nombrecli)) {
                $updates['nombrecli'] = ClienteAuth::normalizeName($request->input('nombrecli'));
            }

            if ($request->filled('email') && empty($clienteExistente->email)) {
                $updates['email'] = $request->input('email');
            }

            if ($updates !== []) {
                $clienteExistente->update($updates);
                $clienteExistente->refresh();
            }

            return response()->json([
                'success' => true,
                'created' => false,
                'message' => 'Cliente ya existia. Se reutilizo el registro.',
                'data' => [
                    'cliente' => [
                        'idcli' => $clienteExistente->idcli,
                        'nombrecli' => $clienteExistente->nombrecli,
                        'telefonocli' => $clienteExistente->telefonocli,
                        'email' => $clienteExistente->email,
                    ],
                ],
            ]);
        }

        $nombre = $request->filled('nombrecli')
            ? ClienteAuth::normalizeName($request->input('nombrecli'))
            : $this->nextWhatsappClientName();

        $cliente = Cliente::create([
            'nombrecli' => $nombre,
            'telefonocli' => $telefono,
            'email' => $request->input('email'),
            'saldo' => 0,
        ]);

        return response()->json([
            'success' => true,
            'created' => true,
            'message' => 'Cliente creado correctamente.',
            'data' => [
                'cliente' => [
                    'idcli' => $cliente->idcli,
                    'nombrecli' => $cliente->nombrecli,
                    'telefonocli' => $cliente->telefonocli,
                    'email' => $cliente->email,
                ],
            ],
        ], 201);
    }

    private function nextWhatsappClientName(): string
    {
        $prefix = 'Cliente WhatsApp';
        $names = Cliente::query()
            ->where('nombrecli', 'like', $prefix . '%')
            ->pluck('nombrecli')
            ->all();

        $used = [];
        foreach ($names as $name) {
            $normalized = trim((string) $name);

            if ($normalized === $prefix) {
                $used[0] = true;
                continue;
            }

            if (preg_match('/^Cliente WhatsApp\s+(\d+)$/', $normalized, $matches) === 1) {
                $used[(int) $matches[1]] = true;
            }
        }

        $sequence = 1;
        while (isset($used[$sequence])) {
            $sequence++;
        }

        return $prefix . ' ' . $sequence;
    }
}
