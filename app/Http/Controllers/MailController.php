<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Models\Mail;
class MailController extends Controller
{
    public function index()
    {
        if (!Gate::allows('mails.index')) {
            abort(403, 'No tienes permiso para ver los correos.');
        }
        $mails = Mail::all();
        return view('inventory.cuentas.mails', compact('mails'));
    }

    public function store(Request $request)
    {
        if (!Gate::allows('mails.store')) {
            abort(403, 'No tienes permiso para crear correos.');
        }
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|max:255',
            'host' => 'required|string',
            'description' => 'nullable|string|max:255',
        ]);
        Mail::create($request->all());
        return redirect()->route('mails.index')->with('success', 'Correo creado correctamente.');
    }

    public function update(Request $request, Mail $mail)
    {
        if (!Gate::allows('mails.update')) {
            abort(403, 'No tienes permiso para actualizar correos.');
        }
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|max:255',
            'host' => 'required|string',
            'description' => 'nullable|string|max:255',
        ]);
        $mail->update($request->all());
        return redirect()->route('mails.index')->with('success', 'Correo actualizado correctamente.');
    }

    public function destroy(Mail $mail)
    {
        if (!Gate::allows('mails.destroy')) {
            abort(403, 'No tienes permiso para eliminar correos.');
        }
        $mail->delete();
        return redirect()->route('mails.index')->with('success', 'Correo eliminado correctamente.');
    }
}
