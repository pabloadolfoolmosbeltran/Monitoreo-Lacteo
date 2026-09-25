<?php

namespace App\Policies;

use App\Models\AjusteInventario;
use App\Models\User;

class AjusteInventarioPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->rol, ['Administrador', 'Trabajador'], true);
    }

    public function view(User $user, AjusteInventario $ajuste): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, AjusteInventario $ajuste): bool
    {
        return $user->rol === 'Administrador' && $ajuste->estado === 'activo';
    }

    public function delete(User $user, AjusteInventario $ajuste): bool
    {
        return $this->update($user, $ajuste);
    }
}
