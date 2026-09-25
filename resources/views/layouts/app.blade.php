<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Control') | Producción Láctea</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/css/dashboard.css',
        'resources/css/produccion.css',
        'resources/css/control.css',
        'resources/css/eventos.css',
        'resources/css/productos.css',
        'resources/css/presentaciones.css',
        'resources/css/usuarios.css',
        'resources/css/reportes-show.css',
        'resources/css/comercial.css',
        'resources/js/app.js',
        'resources/js/dashboard.js',
        'resources/js/produccion.js',
        'resources/js/control.js',
        'resources/js/productos.js',
        'resources/js/comercial.js',
    ])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('styles')
</head>
<body class="panel-layout" data-layout="panel-principal" data-panel-navigation>
    @include('partials.navbar')

    <div id="app-shell" class="container-fluid overflow-hidden">
        <div class="row h-100">
            <!-- Sidebar: Ocupa 2 columnas -->
            <aside id="app-sidebar" class="col-md-2 p-0 sidebar shadow-sm">
                @include('partials.sidebar')
            </aside>
            
            <!-- Main Content: Ocupa 10 columnas -->
            <main id="app-main" class="col-md-10 main-content">
                @hasSection('commercial-panel')
                    <div class="commercial-workspace">
                        <div class="commercial-page-heading">
                            <div>
                                <p class="commercial-eyebrow">DEL PRODUCTOR A TU MOSTRADOR</p>
                                <h1>@yield('title', 'Gestión comercial')</h1>
                                <p class="text-muted mb-0">@yield('subtitle', 'Trazabilidad y control en cada operación.')</p>
                            </div>
                            <div class="commercial-page-actions">@yield('actions')</div>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                        @endif
                        <div id="feedback" role="status" aria-live="polite"></div>

                        @yield('content')
                    </div>
                @else
                    @yield('content')
                @endif
            </main>
        </div>
    </div>

    @include('partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
