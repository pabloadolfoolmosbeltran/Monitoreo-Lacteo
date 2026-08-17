<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Muestra el formulario de inicio de sesión.
     */
    /**
     * Muestra el formulario de inicio de sesión.
     */
            public function login()
        {
            if (Auth::check()) {

                return redirect()->route('dashboard');

            }

            return view('auth.login');
        }

    /**
     * Procesa el intento de autenticación (Se programará en la siguiente fase).
     */
    /**
 * Procesa el inicio de sesión.
 */
        public function autenticar(Request $request)
        {
            /*
            |--------------------------------------------------------------------------
            | Validar datos del formulario
            |--------------------------------------------------------------------------
            */

            $credenciales = $request->validate([

                'email' => 'required|email',

                'password' => 'required',

            ]);

            /*
            |--------------------------------------------------------------------------
            | Intentar iniciar sesión
            |--------------------------------------------------------------------------
            */

            if (Auth::attempt($credenciales)) {

                /*
                |--------------------------------------------------------------------------
                | Regenerar sesión por seguridad
                |--------------------------------------------------------------------------
                */

                $request->session()->regenerate();

                return redirect()->route('dashboard');

            }

            /*
            |--------------------------------------------------------------------------
            | Credenciales incorrectas
            |--------------------------------------------------------------------------
            */

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Correo o contraseña incorrectos.'
                );
        }

    /**
     * Cierra la sesión del usuario (Se programará en la siguiente fase).
     */
    /**
 * Cerrar sesión.
 */
        public function logout(Request $request)
        {
            Auth::logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return redirect()->route('login');
        }
}
