@extends('layouts.app')

@section('content')

<h2 class="mb-4">
Editar Producto
</h2>

<div class="card shadow-sm">
    <div class="card-body">
        <form
            action="{{ url('/productos/'.$producto->id) }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf

            @method('PUT')

            @include('productos.form')

            <button class="btn btn-primary">
                Actualizar Producto
            </button>

            <a
                href="{{ url('/productos') }}"
                class="btn btn-secondary">
                Cancelar
            </a>

        </form>
    </div>
</div>

@endsection