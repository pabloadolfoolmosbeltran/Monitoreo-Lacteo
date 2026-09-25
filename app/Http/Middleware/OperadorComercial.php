<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class OperadorComercial
{
    public function handle(Request $request,Closure $next)
    {
        abort_unless($request->user()?->activo && in_array($request->user()->rol,['Administrador','Trabajador'],true),403);
        return $next($request);
    }
}

