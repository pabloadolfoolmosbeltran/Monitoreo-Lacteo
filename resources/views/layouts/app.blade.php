<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Control | Producción Láctea</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        /* Paleta Industrial y de Enfriamiento */
        :root {
            --sidebar-bg: #1e293b; /* Azul pizarra muy oscuro */
            --sidebar-hover: #334155;
            --accent-blue: #0ea5e9; /* Azul frío (enfriador) */
            --bg-body: #f4f6f9; /* Gris muy claro, aspecto limpio */
        }
        
        body {
            background-color: var(--bg-body);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        /* Estilos del Menú Lateral (Sidebar) */
        .sidebar {
            background-color: var(--sidebar-bg);
            min-height: 100vh;
            color: #cbd5e1;
            padding-top: 1rem;
        }
        .sidebar-heading {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #64748b;
            font-weight: 700;
            padding: 0 1rem;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .sidebar .nav-link {
            color: #cbd5e1;
            padding: 0.6rem 1rem;
            border-radius: 6px;
            margin: 0.2rem 0.75rem;
            transition: all 0.2s ease-in-out;
            font-size: 0.9rem;
        }
        .sidebar .nav-link:hover {
            background-color: var(--sidebar-hover);
            color: #fff;
        }
        .sidebar .nav-link.active {
            background-color: var(--sidebar-hover);
            color: #fff;
            border-left: 4px solid var(--accent-blue);
            padding-left: calc(1rem - 4px); /* Mantiene la alineación */
            font-weight: 600;
        }
        .sidebar .nav-link i {
            width: 20px;
            display: inline-block;
            text-align: center;
            margin-right: 8px;
            font-size: 1.1rem;
        }

        /* Barra de Navegación Superior */
        .navbar-custom {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }
        
        /* Contenedor Principal */
        .main-content {
            padding: 2rem;
        }
    </style>
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
</body>
</html>