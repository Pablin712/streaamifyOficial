<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\ChatWhatsappChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatWhatsappChannelController extends Controller
{
    public function index()
    {
        $channels = ChatWhatsappChannel::query()
            ->orderByDesc('is_active')
            ->orderBy('instance_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $channels,
        ]);
    }

    public function upsert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'instance_name' => 'required|string|max:120',
            'display_name' => 'nullable|string|max:120',
            'api_key' => 'required|string|max:191',
            'server_url' => 'nullable|string|max:191',
            'color' => 'nullable|in:verde,azul,naranja,otro',
            'is_active' => 'nullable|boolean',
            'outbound_enabled' => 'nullable|boolean',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $instanceName = trim((string) $request->input('instance_name'));

        $channel = ChatWhatsappChannel::query()->updateOrCreate(
            ['instance_name' => $instanceName],
            [
                'display_name' => $request->input('display_name'),
                'api_key' => $request->input('api_key'),
                'server_url' => $request->input('server_url', config('services.evoapi.base_url')),
                'color' => $request->input('color', 'otro'),
                'is_active' => (bool) $request->input('is_active', true),
                'outbound_enabled' => (bool) $request->input('outbound_enabled', true),
                'metadata' => $request->input('metadata'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Canal WhatsApp guardado correctamente.',
            'data' => $channel,
        ]);
    }

    public function destroy(int $id)
    {
        $channel = ChatWhatsappChannel::query()->find($id);

        if (!$channel) {
            return response()->json([
                'success' => false,
                'message' => 'Canal no encontrado.',
            ], 404);
        }

        $channel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Canal eliminado.',
        ]);
    }
}
