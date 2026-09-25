@extends('layouts.app')


@section('content')

<h2 class="mb-4">Editar Usuario</h2>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
            @csrf
            @method('PUT')

            @include('usuarios.form')

            <button class="btn btn-warning">
                Actualizar Usuario
            </button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                Cancelar
            </a>
        </form>
    </div>
</div>

@endsection
