<?php

namespace App\Services\Donna\Google;

use App\Models\DonnaIntegration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DonnaGoogleTokenService
{
    public function getValidAccessToken(DonnaIntegration $integ): ?string
    {
        if (!$integ->isTokenExpired()) {
            return Crypt::decryptString($integ->access_token_encrypted);
        }

        return $this->refresh($integ);
    }

    private function refresh(DonnaIntegration $integ): ?string
    {
        if (!$integ->refresh_token_encrypted) {
            $integ->update(['status' => 'error', 'last_error' => 'No refresh token available.']);
            return null;
        }

        try {
            $refreshToken = Crypt::decryptString($integ->refresh_token_encrypted);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id'     => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
            ]);

            if (!$response->successful()) {
                $error = $response->json('error', 'unknown');
                $integ->update([
                    'status'     => in_array($error, ['invalid_grant', 'token_revoked']) ? 'revoked' : 'error',
                    'last_error' => "Google refresh failed: {$error}",
                ]);
                return null;
            }

            $data       = $response->json();
            $newToken   = $data['access_token'];
            $expiresIn  = $data['expires_in'] ?? 3600;

            $integ->update([
                'access_token_encrypted' => Crypt::encryptString($newToken),
                'token_expires_at'       => now()->addSeconds($expiresIn),
                'status'                 => 'active',
                'last_error'             => null,
            ]);

            return $newToken;
        } catch (\Throwable $e) {
            Log::error('DonnaGoogleTokenService refresh failed', ['error' => $e->getMessage()]);
            $integ->update(['status' => 'error', 'last_error' => $e->getMessage()]);
            return null;
        }
    }
}
