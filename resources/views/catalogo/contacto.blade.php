@extends('catalogo.layout')

@section('title', 'Contáctanos')

@section('content')
<div class="container py-5">
    <section class="hero p-4 p-md-5 mb-5"><p class="text-uppercase small fw-bold text-success">Contáctanos</p><h1 class="display-5 fw-bold">Ubicación de nuestros productores</h1><p class="lead mb-0">Encuentra las unidades productivas que tienen información de contacto registrada.</p></section>
    <div class="row g-4">
        @forelse($productores as $productor)
            <div class="col-md-6"><article class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <p class="text-success fw-bold small text-uppercase mb-2">
                        Unidad productiva
                    </p>
                    <h2 class="h4">
                        {{ $productor->nombre_unidad_productiva }}
                    </h2>
                    <p class="mb-2">
                        <i class="bi bi-geo-alt me-2 text-success">

                        </i>
                        {{ $productor->direccion }}
                    </p>
                    @if($productor->telefono)
                        <p class="mb-0">
                            <i class="bi bi-telephone me-2 text-success">
                            </i>
                            {{ $productor->telefono }}
                        </p>
                    @endif
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light border">
                    Aún no hay ubicaciones publicadas.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
