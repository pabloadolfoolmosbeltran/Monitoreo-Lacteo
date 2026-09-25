@extends('layouts.app')


@section('content')

<h2 class="mb-4">Nuevo Usuario</h2>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('usuarios.store') }}" method="POST">
            @csrf

            @include('usuarios.form')

            <button class="btn btn-success">
                Guardar Usuario
            </button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                Cancelar
            </a>
        </form>
    </div>
</div>

@endsection
