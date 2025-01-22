<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\RecoverMail;
use App\Mail\RecoverClienteMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\Empleado;
use App\Models\Cliente;

class EmailController extends Controller
{
    public function sendRecoverEmail(Request $request)
    {
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

        // Generar una nueva contraseña
        $password = $this->generarContrasenia(); // Aquí usas tu método o lógica para generar la contraseña

        // Actualizar la contraseña en la base de datos
        $empleado->passwordemp = $password;
        $empleado->save();

        // Enviar el correo con la nueva contraseña
        Mail::to($email)->send(new RecoverMail($empleado, $password));

        // Redirigir con un mensaje de éxito
        return redirect()->route('recover')->with('success', 'Email sent successfully. Please check your inbox.');
    }
    public function sendRecoverClienteEmail(Request $request){
        // Obtener el email del request
        $email = $request->input('email');

        // Buscar al cliente asociado al email
        $cliente = Cliente::where('email', $email)->first();

        // Verificar si el cliente existe
        if (!$cliente) {
            return redirect()->route('recover')->with('error', 'No client found with this email');
        }

        // Verificar si el cliente existe y tiene un email válido
        if (!$cliente || empty($cliente->email)) {
            return redirect()->route('recover')->with('error', 'No client found or invalid email');
        }

        // Generar una nueva contraseña
        $password = $this->generarContrasenia(); // Aquí usas tu método o lógica para generar la contraseña

        // Actualizar la contraseña en la base de datos
        $cliente->password = $password;
        $cliente->save();

        // Enviar el correo con la nueva contraseña
        Mail::to($email)->send(new RecoverClienteMail($cliente, $password));

        // Redirigir con un mensaje de éxito
        return redirect()->route('cliente.recover')->with('success', 'Email sent successfully. Please check your inbox.');
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
