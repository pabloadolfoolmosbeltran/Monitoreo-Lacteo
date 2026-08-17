@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h2>

        Productos

    </h2>

    <a
        href="{{ url('/productos/create') }}"
        class="btn btn-success">

        Nuevo Producto

    </a>

</div>

@if(session('success'))

<div class="alert alert-success">

    {{ session('success') }}

</div>

@endif
@if(session('error'))

<div class="alert alert-danger">

    {{ session('error') }}

</div>

@endif

<form method="GET" class="mb-4">

    <div class="row">

        <div class="col-md-6">

            <input
                type="text"
                name="buscar"
                class="form-control"
                placeholder="Buscar producto..."
                value="{{ request('buscar') }}">

        </div>

        <div class="col-md-2">

            <button class="btn btn-primary w-100">

                Buscar

            </button>

        </div>

        <div class="col-md-2">

            <a
                href="{{ url('/productos') }}"
                class="btn btn-secondary w-100">

                Limpiar

            </a>

        </div>

    </div>

</form>

<div class="card shadow-sm">

    <div class="card-body">

        <table class="table table-hover">

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Nombre</th>

                    <th>Temperatura Mínima</th>

                    <th>Temperatura Máxima</th>

                    <th>Temperatura Pasteurización</th>

                    <th>Estado</th>

                    <th width="180">

                        Acciones

                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($productos as $producto)

                <tr>

                    <td>

                        {{ $producto->id }}

                    </td>

                    <td>

                        {{ $producto->nombre }}

                    </td>

                    <td>

                        {{ $producto->temperatura_minima }} °C

                    </td>

                    <td>

                        {{ $producto->temperatura_maxima }} °C

                    </td>

                    <td>

                        {{ $producto->temperatura_pasteurizacion }} °C

                    </td>

                    <td>

                        @if($producto->activo)

                            <span class="badge bg-success">

                                Activo

                            </span>

                        @else

                            <span class="badge bg-danger">

                                Inactivo

                            </span>

                        @endif

                    </td>

                    <td>

                        <a
                            href="{{ url('/productos/'.$producto->id.'/edit') }}"
                            class="btn btn-warning btn-sm">

                            Editar

                        </a>

                        <form
                            action="{{ url('/productos/'.$producto->id) }}"
                            method="POST"
                            class="d-inline">

                            @csrf

                            @method('DELETE')

                            <button
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('¿Eliminar este producto?')">

                                Eliminar

                            </button>

                        </form>

                    </td>

                </tr>

                @empty

                <tr>

                    <td colspan="7" class="text-center">

                        No existen productos registrados.

                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>

        {{ $productos->links() }}

    </div>

</div>

@endsection
