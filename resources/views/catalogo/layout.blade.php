<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Catálogo') | Producción Láctea</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/catalogo.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg catalog-nav sticky-top">
        <div class="container py-2">
            <a class="navbar-brand brand-mark" href="{{ route('catalogo.index') }}"><i class="bi bi-droplet-half me-2"></i>Lácteos Locales</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#catalogNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="catalogNav">
                <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <a class="nav-link {{ request()->routeIs('catalogo.index') ? 'active' : '' }}" href="{{ route('catalogo.index') }}">Productos</a>
                    <a class="nav-link {{ request()->routeIs('catalogo.nosotros') ? 'active' : '' }}" href="{{ route('catalogo.nosotros') }}">Nosotros</a>
                    <a class="nav-link {{ request()->routeIs('catalogo.contacto') ? 'active' : '' }}" href="{{ route('catalogo.contacto') }}">Contáctanos</a>
                    <a class="btn btn-outline-success btn-sm ms-lg-2" href="{{ route('login') }}">Ingresar</a>
                </div>
            </div>
        </div>
    </nav>
    <main>@yield('content')</main>
    <footer class="footer mt-5 py-4"><div class="container small">Sistema Inteligente de Producción Láctea &copy; {{ date('Y') }}</div></footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
