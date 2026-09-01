@extends('catalogo.layout')

@section('title', 'Nosotros')

@section('content')
<div class="container py-5">
    <section class="hero p-4 p-md-5 mb-5">
        <p class="text-uppercase small fw-bold text-success">
            Nuestra producción
        </p>
        <h1 class="display-5 fw-bold">
            Producción láctea con trazabilidad
        </h1>
        <p class="lead mb-0">
            Productor local.
        </p>
    </section>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <i class="bi bi-people-fill text-success fs-2"></i>
                    <h2 class="h4 mt-3">Productores locales</h2>
                    <p class="mb-0 text-muted">
                        Visibilizamos las presentaciones comerciales elaboradas por nuestra comunidad productora.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <i class="bi bi-thermometer-half text-success fs-2"></i>
                    <h2 class="h4 mt-3">
                        Control del proceso
                    </h2>   
                    <p class="mb-0 text-muted">
                        El sistema monitorea las condiciones térmicas durante la producción para apoyar la calidad del lote.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <i class="bi bi-bag-check-fill text-success fs-2"></i>
                    <h2 class="h4 mt-3">Información clara</h2>
                    <p class="mb-0 text-muted">
                        Cada presentación muestra su precio y disponibilidad para facilitar la consulta de los visitantes.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
