<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">
            Nombre Completo
        </label>
        <input
            type="text"
            name="name"
            class="form-control"
            value="{{ old('name', $usuario->name ?? '') }}"
            required>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">
            Correo Electrónico
        </label>
        <input
            type="email"
            name="email"
            class="form-control"
            value="{{ old('email', $usuario->email ?? '') }}"
            autocomplete="new-email"
            required>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">
            Contraseña
        </label>
        <input
            type="password"
            name="password"
            minlength="8"
            autocomplete="new-password"
            class="form-control"
            {{ isset($usuario) ? '' : 'required' }}>

        @isset($usuario)
        <small class="text-muted">
            Contraseña mínima de 8 caracteres. Déjelo vacío si no desea cambiar la contraseña.
        </small>
        @else
        <small class="text-muted">
            La contraseña debe tener un mínimo de 8 caracteres.
        </small>
        @endisset
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">
            Confirmar Contraseña
        </label>
        <input
            type="password"
            name="password_confirmation"
            minlength="8"
            autocomplete="new-password"
            class="form-control"
            {{ isset($usuario) ? '' : 'required' }}>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">
            Teléfono
        </label>
        <input
            type="text"
            name="telefono"
            class="form-control"
            value="{{ old('telefono', $usuario->telefono ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">
            Rol
        </label>
        <select
            name="rol"
            class="form-select">
            <option value="Trabajador"
                {{ old('rol', $usuario->rol ?? '') == 'Trabajador' ? 'selected' : '' }}>
                Trabajador
            </option>
            <option value="Administrador"
                {{ old('rol', $usuario->rol ?? '') == 'Administrador' ? 'selected' : '' }}>
                Administrador
            </option>
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">
        Dirección
    </label>
    <input
        type="text"
        name="direccion"
        class="form-control"
        value="{{ old('direccion', $usuario->direccion ?? '') }}">
</div>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
