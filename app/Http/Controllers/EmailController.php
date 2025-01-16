<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\RecoverMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\Empleado; 

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
            return response()->json(['error' => 'No employee found with this email'], 404);
        }
       

        // Verificar si el empleado existe y tiene un email válido
        if (!$empleado || empty($empleado->email)) {
            return response()->json(['error' => 'No employee found or invalid email'], 404);
        }

        // Generar una nueva contraseña
        $password = $this->generarContrasenia(); // Aquí usas tu método o lógica para generar la contraseña

        // Actualizar la contraseña en la base de datos
        $empleado->passwordemp = $password;
        $empleado->save();

        // Enviar el correo con la nueva contraseña
        Mail::to($email)->send(new RecoverMail($empleado, $password));

        // Responder con éxito
        return response()->json(['message' => 'Email sent successfully'], 200);
    }
    protected function generarContrasenia($longitud = 8)
    {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
        $caracteresLongitud = strlen($caracteres);
        $contrasenia = '';
    
        for ($i = 0; $i < $longitud; $i++) {
            $contrasenia .= $caracteres[random_int(0, $caracteresLongitud - 1)];
        }
    
        return $contrasenia;
    }
}
