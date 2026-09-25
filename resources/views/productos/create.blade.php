@extends('layouts.app')


@section('content')

<h2 class="mb-4">
Nuevo Producto
</h2>

<div class="card shadow-sm">
    <div class="card-body">
        <form
            action="{{ url('/productos') }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf

            @include('productos.form')

            <button class="btn btn-success">
                Guardar Producto
            </button>

            <a
                href="{{ url('/productos') }}"
                class="btn btn-secondary">
                ← Regresar
            </a>

        </form>
    </div>
</div>

@endsection
