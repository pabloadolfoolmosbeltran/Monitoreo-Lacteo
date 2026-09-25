<?php

namespace App\Policies;

use App\Models\DescarteProducto;
use App\Models\User;

class DescarteProductoPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->rol, ['Administrador', 'Trabajador'], true);
    }

    public function view(User $user, DescarteProducto $descarte): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, DescarteProducto $descarte): bool
    {
        return $user->rol === 'Administrador' && $descarte->estado === 'pendiente';
    }

    public function delete(User $user, DescarteProducto $descarte): bool
    {
        return $this->update($user, $descarte);
    }

    public function process(User $user, DescarteProducto $descarte): bool
    {
        return $this->update($user, $descarte);
    }
}
