<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'buscar' => 'nullable|string|max:100',
            'estado' => 'nullable|in:eliminados',
        ]);

        $eliminados = $request->input('estado') === 'eliminados';
        abort_unless($request->user()?->rol === 'Administrador', 403);

        $consulta = User::where('activo', ! $eliminados)
            ->whereIn('rol', ['Administrador', 'Trabajador']);

        if ($request->filled('buscar')) {
            $consulta->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->buscar . '%')
                      ->orWhere('email', 'like', '%' . $request->buscar . '%');
            });
        }

        $usuarios = $consulta->orderBy('name')
                             ->paginate(10)
                             ->withQueryString();

        return view('usuarios.index', compact('usuarios', 'eliminados'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $datos = $this->validarUsuario($request);
        $datos['password'] = Hash::make($datos['password']);
        $datos['activo'] = true;

        User::create($datos);

        return redirect()->route('usuarios.index')->with('success', 'Usuario registrado correctamente.');
    }

    public function edit(User $usuario)
    {
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        $datos = $this->validarUsuario($request, $usuario);

        if (! empty($datos['password'])) {
            $datos['password'] = Hash::make($datos['password']);
        } else {
            unset($datos['password']);
        }

        $usuario->update($datos);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario)
    {
        if (Auth::id() == $usuario->getKey()) {
            return redirect()->route('usuarios.index')->with('error', 'No puede eliminar su propio usuario.');
        }

        // Eliminación lógica: no se borra el usuario ni su historial.
        $usuario->update(['activo' => false]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario desactivado correctamente.');
    }

    public function restore(Request $request, User $usuario)
    {
        abort_unless($request->user()?->rol === 'Administrador', 403);

        $usuario->update(['activo' => true]);

        return redirect()->route('usuarios.index', ['estado' => 'eliminados'])
            ->with('success', 'Usuario restablecido correctamente.');
    }

    // APUNTE:
    // Este método valida el formulario de usuarios. Al editar, ignora el correo
    // del usuario actual para no marcarlo como duplicado contra sí mismo.
    private function validarUsuario(Request $request, ?User $usuario = null): array
    {
        $correoUnico = Rule::unique('users', 'email');

        if ($usuario) {
            $correoUnico->ignore($usuario);
        }

        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                $correoUnico,
            ],
            'password' => [$usuario ? 'nullable' : 'required', 'confirmed', 'min:8'],
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'rol' => 'required|in:Administrador,Trabajador',
        ]);
    }
}
