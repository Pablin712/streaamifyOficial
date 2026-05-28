<?php

namespace App\Services\Donna;

use App\Models\DonnaToolLog;

class DonnaToolLogger
{
    public function log(
        string $toolName,
        int $clientId,
        array $request,
        array $response,
        ?int $subscriptionId = null,
        ?int $channelId = null,
        ?int $durationMs = null
    ): void {
        // Strip sensitive data before storing
        $safeRequest = $this->sanitize($request);

        DonnaToolLog::create([
            'client_id'       => $clientId,
            'subscription_id' => $subscriptionId,
            'channel_id'      => $channelId,
            'tool_name'       => $toolName,
            'request_json'    => $safeRequest,
            'response_json'   => $this->sanitizeResponse($response),
            'success'         => $response['success'] ?? false,
            'reason'          => $response['reason'] ?? null,
            'duration_ms'     => $durationMs,
        ]);
    }

    private function sanitize(array $data): array
    {
        $sensitive = ['access_token', 'refresh_token', 'api_key', 'token', 'password', 'secret'];
        foreach ($sensitive as $key) {
            if (isset($data[$key])) $data[$key] = '[REDACTED]';
        }
        return $data;
    }

    private function sanitizeResponse(array $data): array
    {
        unset($data['access_token'], $data['refresh_token']);
        if (isset($data['google_error'])) {
            $data['google_error'] = array_intersect_key($data['google_error'], array_flip(['error', 'code', 'status']));
        }
        return $data;
    }
}
