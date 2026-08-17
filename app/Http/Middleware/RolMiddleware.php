<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next,
        string $rol
    ): Response
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return redirect()->route('login');
        }

        // 🛡️ Validamos el rol directamente sobre el atributo de la tabla users unificada
        if ($usuario->rol != $rol) {
            abort(403);
        }

        return $next($request);
    }
}