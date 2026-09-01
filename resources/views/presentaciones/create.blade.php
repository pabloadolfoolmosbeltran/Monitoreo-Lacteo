@extends('layouts.app')

@section('content')
<h2 class="mb-4">Nueva presentación</h2>
<div class="card shadow-sm"><div class="card-body"><form method="POST" action="{{ route('presentaciones.store') }}" enctype="multipart/form-data">@csrf @include('presentaciones.form')<div class="mt-4"><button class="btn btn-success">Guardar presentación</button><a href="{{ route('presentaciones.index') }}" class="btn btn-secondary">Cancelar</a></div></form></div></div>
@endsection
