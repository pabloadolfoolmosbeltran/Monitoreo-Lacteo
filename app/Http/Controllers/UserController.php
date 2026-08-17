<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Mostrar listado de usuarios con soporte de búsqueda.
     */
    public function index(Request $request)
    {
        // 1. Iniciamos la consulta directamente sobre el modelo User unificado
        $consulta = User::query();

        // 2. Si el usuario escribió algo en el buscador, filtramos
        if ($request->filled('buscar')) {
            $consulta->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->buscar . '%')
                      ->orWhere('email', 'like', '%' . $request->buscar . '%')
                      ->orWhere('nombre_unidad_productiva', 'like', '%' . $request->buscar . '%');
            });
        }

        // 3. Ordenamos por nombre, paginamos de 10 en 10 y mantenemos los filtros en la URL
        $usuarios = $consulta->orderBy('name')
                             ->paginate(10)
                             ->withQueryString();

        // 4. Retornamos la vista con los datos compactados
        return view('usuarios.index', compact('usuarios'));
    }

    /**
     * Mostrar formulario para crear un usuario.
     */
    public function create()
    {
        return view('usuarios.create');
    }

    /**
     * Guardar un nuevo usuario.
     */
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
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with(
                'success',
                'Usuario registrado correctamente.'
            );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Mostrar formulario para editar un usuario.
     */
    public function edit(User $usuario)
    {
        return view(
            'usuarios.edit',
            compact('usuario')
        );
    }

    /**
     * Actualizar usuario.
     */
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
            $request->validate([
                'password' => 'confirmed|min:8'
            ]);

            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()
            ->route('usuarios.index')
            ->with(
                'success',
                'Usuario actualizado correctamente.'
            );
    }

    /**
     * Eliminar usuario.
     */
    public function destroy(User $usuario)
    {
        if (Auth::id() == $usuario->getKey()) {
            return redirect()
                ->route('usuarios.index')
                ->with(
                    'error',
                    'No puede eliminar su propio usuario.'
                );
        }

        $usuario->delete();

        return redirect()
            ->route('usuarios.index')
            ->with(
                'success',
                'Usuario eliminado correctamente.'
            );
    }
}