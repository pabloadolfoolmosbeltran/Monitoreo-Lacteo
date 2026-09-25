@extends('layouts.app')

@section('commercial-panel', '1')

@section('title', 'Registrar producto descartado')

@section('subtitle', 'La pérdida se calcula con el costo registrado en el lote.')

@section('content')
    <form class="panel" method="POST" action="{{ route('descartes-productos.store') }}">
        @include('descartes-productos._form')
    </form>
@endsection