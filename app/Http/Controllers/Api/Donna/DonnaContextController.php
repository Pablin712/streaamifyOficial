<?php

namespace App\Http\Controllers\Api\Donna;

use App\Http\Controllers\Controller;
use App\Models\DonnaIntegration;
use App\Services\Donna\DonnaPersonalContextService;
use App\Services\Donna\DonnaServiceValidator;
use Illuminate\Http\Request;

class DonnaContextController extends Controller
{
    public function __construct(
        private DonnaServiceValidator      $validator,
        private DonnaPersonalContextService $contextService
    ) {}

    public function show(Request $request)
    {
        $telegramChatId = $request->query('telegram_chat_id');

        if (!$telegramChatId) {
            return response()->json([
                'success' => false,
                'allowed' => false,
                'reason'  => 'missing_telegram_chat_id',
                'message' => 'Parámetro telegram_chat_id requerido.',
            ], 422);
        }

        $validation = $this->validator->resolveByTelegram((string) $telegramChatId);

        if (!$validation['allowed']) {
            $response = [
                'success' => false,
                'allowed' => false,
                'reason'  => $validation['reason'],
                'message' => $validation['message'],
            ];
            if (isset($validation['plan'])) $response['plan'] = $validation['plan'];
            return response()->json($response);
        }

        $channel = $validation['channel'];
        $sub     = $validation['sub'];

        // Check Google connection
        $integ = DonnaIntegration::where('client_id', $channel->client_id)
            ->where('integration_type', 'google')
            ->where('status', 'active')
            ->first();

        if (!$integ) {
            return response()->json([
                'success'              => false,
                'allowed'              => false,
                'reason'               => 'google_not_connected',
                'message'              => 'Donna Personal está activa, pero aún debes conectar tu cuenta de Google en Streamify para usar Calendar y Sheets.',
                'connect_google_url'   => config('app.url') . '/donna',
            ]);
        }

        return response()->json($this->contextService->build($channel, $sub));
    }
}
