<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Control | Producción Láctea</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    @include('partials.navbar')

    <div class="container-fluid overflow-hidden">
        <div class="row">
            <!-- Sidebar: Ocupa 2 columnas -->
            <div class="col-md-2 p-0 sidebar shadow-sm">
                @include('partials.sidebar')
            </div>
            
            <!-- Main Content: Ocupa 10 columnas -->
            <main class="col-md-10 main-content h-100 overflow-auto">
                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>