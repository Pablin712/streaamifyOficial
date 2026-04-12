<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\RecoverMail;
use App\Mail\RecoverClienteMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Empleado;
use App\Models\Cliente;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailController extends Controller
{
    public function sendRecoverEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Obtener el email del request
        $email = $request->input('email');

        // Buscar al empleado asociado al email
        $empleado = Empleado::where('email', $email)->first();

        // Verificar si el empleado existe
        if (!$empleado) {
            return redirect()->route('recover')->with('error', 'No employee found with this email');
        }

        // Verificar si el empleado existe y tiene un email válido
        if (!$empleado || empty($empleado->email)) {
            return redirect()->route('recover')->with('error', 'No employee found or invalid email');
        }

        // Guardar hash anterior para poder revertir si falla el envío
        $hashAnterior = $empleado->passwordemp;

        // Generar nueva contraseña y actualizar
        $password = $this->generarContrasenia();
        $empleado->passwordemp = $password;
        $empleado->save();

        try {
            Mail::to($email)->send(new RecoverMail($empleado, $password));
        } catch (TransportExceptionInterface $e) {
            DB::table('empleados')->where('idemp', $empleado->idemp)->update(['passwordemp' => $hashAnterior]);

            Log::warning('Fallo SMTP en recuperación admin', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            $mensajeError = str_contains(strtolower($e->getMessage()), 'disabled by user')
                ? 'El buzón remitente está suspendido en hPanel. Actívalo o usa un buzón SMTP activo.'
                : 'No se pudo enviar el correo de recuperación. Verifica la configuración del buzón en hosting.';

            return redirect()->route('recover')->with('error', $mensajeError);
        } catch (\Throwable $e) {
            DB::table('empleados')->where('idemp', $empleado->idemp)->update(['passwordemp' => $hashAnterior]);

            Log::error('Error inesperado en recuperación admin', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('recover')->with('error', 'Ocurrió un error al procesar la recuperación.');
        }

        // Redirigir con un mensaje de éxito
        return redirect()->route('recover')->with('success', 'Email sent successfully. Please check your inbox.');
    }
    public function sendRecoverClienteEmail(Request $request){
        $request->validate([
            'email' => 'required|email',
        ]);

        // Obtener el email del request
        $email = $request->input('email');

        // Buscar al cliente asociado al email
        $cliente = Cliente::where('email', $email)->first();

        // Verificar si el cliente existe
        if (!$cliente) {
            return redirect()->route('cliente.recover')->with('error', 'No se encontró una cuenta de cliente con ese correo.');
        }

        // Verificar si el cliente existe y tiene un email válido
        if (!$cliente || empty($cliente->email)) {
            return redirect()->route('cliente.recover')->with('error', 'Cliente no encontrado o correo inválido.');
        }

        // Guardar hash anterior para poder revertir si falla el envío
        $hashAnterior = $cliente->password;

        // Generar nueva contraseña y actualizar
        $password = $this->generarContrasenia();
        $cliente->password = $password;
        $cliente->save();

        try {
            Mail::to($email)->send(new RecoverClienteMail($cliente, $password));
        } catch (TransportExceptionInterface $e) {
            DB::table('clientes')->where('idcli', $cliente->idcli)->update(['password' => $hashAnterior]);

            Log::warning('Fallo SMTP en recuperación cliente', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            $mensajeError = str_contains(strtolower($e->getMessage()), 'disabled by user')
                ? 'El buzón remitente está suspendido en hPanel. Actívalo o cambia MAIL_USERNAME a un buzón activo.'
                : 'No se pudo enviar el correo de recuperación. Intenta más tarde o contacta soporte.';

            return redirect()->route('cliente.recover')->with('error', $mensajeError);
        } catch (\Throwable $e) {
            DB::table('clientes')->where('idcli', $cliente->idcli)->update(['password' => $hashAnterior]);

            Log::error('Error inesperado en recuperación cliente', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('cliente.recover')->with('error', 'Ocurrió un error al procesar la recuperación.');
        }

        // Redirigir con un mensaje de éxito
        return redirect()->route('cliente.recover')->with('success', 'Correo enviado correctamente. Revisa tu bandeja de entrada.');
    }
    protected function generarContrasenia($longitud = 8)
    {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
        $caracteresLongitud = strlen($caracteres);
        $contrasenia = 'pablin';

        for ($i = 0; $i < $longitud; $i++) {
            $contrasenia .= $caracteres[random_int(0, $caracteresLongitud - 1)];
        }

        return $contrasenia;
    }
}
