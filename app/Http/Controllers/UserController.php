<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $consulta = User::where('activo', true);

        if ($request->filled('buscar')) {
            $consulta->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->buscar . '%')
                      ->orWhere('email', 'like', '%' . $request->buscar . '%')
                      ->orWhere('nombre_unidad_productiva', 'like', '%' . $request->buscar . '%');
            });
        }

        $usuarios = $consulta->orderBy('name')
                             ->paginate(10)
                             ->withQueryString();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'nombre_unidad_productiva' => 'nullable|string|max:255',
            'rol' => 'required|in:Administrador,Trabajador',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'nombre_unidad_productiva' => $request->nombre_unidad_productiva,
            'rol' => $request->rol,
            'activo' => true,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario registrado correctamente.');
    }

    public function edit(User $usuario)
    {
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'nombre_unidad_productiva' => 'nullable|string|max:255',
            'rol' => 'required|in:Administrador,Trabajador',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'nombre_unidad_productiva' => $request->nombre_unidad_productiva,
            'rol' => $request->rol,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'confirmed|min:8']);
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

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
}
