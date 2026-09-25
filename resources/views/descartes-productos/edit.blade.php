@extends('layouts.app')

@section('commercial-panel', '1')

@section('title', 'Editar descarte #{{ $descarte->id }}')

@section('subtitle', 'Solo los registros pendientes pueden modificarse.')

@section('content')
    <form class="panel" method="POST" action="{{ route('descartes-productos.update', $descarte) }}">
        @include('descartes-productos._form')
    </form>
@endsection