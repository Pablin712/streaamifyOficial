<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DonnaIntegration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Laravel\Socialite\Facades\Socialite;

class DonnaGoogleController extends Controller
{
    public function redirect(Request $request)
    {
        if (!Auth::guard('cliente')->check()) {
            return redirect()->route('donna')->with('donna_error', 'Debes iniciar sesión para conectar Google.');
        }

        // Guardar plan_type en sesión para redirigir correctamente después
        session(['donna_google_return' => $request->query('return', 'donna')]);

        return Socialite::driver('google')
            ->scopes([
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/spreadsheets',
            ])
            ->with([
                'access_type' => 'offline',
                'prompt'      => 'consent select_account',
            ])
            ->redirect();
    }

    public function callback(Request $request)
    {
        if (!Auth::guard('cliente')->check()) {
            return redirect()->route('donna')->with('donna_error', 'Sesión expirada. Vuelve a intentarlo.');
        }

        $cliente = Auth::guard('cliente')->user();

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('donna')->with('donna_error', 'Error al conectar con Google. Intenta nuevamente.');
        }

        $expiresAt = now()->addSeconds($googleUser->expiresIn ?? 3600);

        DonnaIntegration::updateOrCreate(
            [
                'client_id'        => $cliente->idcli,
                'integration_type' => 'google',
            ],
            [
                'name'                    => $googleUser->getName() . ' <' . $googleUser->getEmail() . '>',
                'access_token_encrypted'  => Crypt::encryptString($googleUser->token),
                'refresh_token_encrypted' => $googleUser->refreshToken
                    ? Crypt::encryptString($googleUser->refreshToken)
                    : null,
                'token_expires_at' => $expiresAt,
                'scopes_json'      => ['calendar', 'spreadsheets'],
                'status'           => 'active',
                'last_error'       => null,
                'metadata_json'    => [
                    'email'     => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->getAvatar(),
                ],
            ]
        );

        return redirect()->route('donna')->with('donna_success',
            '¡Google conectado exitosamente! Ya puedes activar Donna. ' .
            'Cuenta: ' . $googleUser->getEmail()
        );
    }

    public function disconnect(Request $request)
    {
        if (!Auth::guard('cliente')->check()) {
            return redirect()->route('donna');
        }

        $cliente = Auth::guard('cliente')->user();

        DonnaIntegration::where('client_id', $cliente->idcli)
            ->where('integration_type', 'google')
            ->update(['status' => 'revoked', 'last_error' => 'Desconectado por el cliente.']);

        return redirect()->route('donna')->with('donna_success', 'Google desconectado.');
    }
}
