<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect('/produccion');
        }

        return view('auth.login');
    }

    public function autenticar(Request $request)
    {
        $credenciales = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // APUNTE:
        // El campo activo permite deshabilitar usuarios sin borrar su historial.
        // Por eso el login solo acepta cuentas activas.
        $credenciales['activo'] = true;

        $esUsuarioInterno = User::where('email', $credenciales['email'])
            ->where('activo', true)
            ->whereIn('rol', ['Administrador', 'Trabajador'])
            ->exists();

        if ($esUsuarioInterno && Auth::attempt($credenciales)) {
            $request->session()->regenerate();
            return redirect('/produccion');
        }

        return back()
            ->withInput()
            ->with('error', 'Correo o contraseña incorrectos.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('catalogo.index');
    }
}
