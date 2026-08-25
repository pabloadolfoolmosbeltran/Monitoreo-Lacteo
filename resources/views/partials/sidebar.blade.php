<style>
    /* Estilos del Sidebar: Turquesa Oscuro Industrial / HMI */
    .sidebar-container {
        background-color: #112224; /* Turquesa oscuro / Petrol industrial profundo */
        color: #b2dfdb;
        min-height: 100vh;
        border-right: 1px solid #1a383b;
        padding: 1.25rem 0.75rem;
    }

    .sidebar-heading {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #80cbc4; /* Turquesa suave para títulos */
        padding: 0.75rem 0.75rem 0.35rem 0.75rem;
        font-weight: 700;
        margin-top: 1rem;
    }

    .sidebar-container .nav-link {
        color: #b0bec5;
        padding: 0.65rem 0.85rem;
        border-radius: 8px;
        margin-bottom: 0.25rem;
        font-size: 0.92rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
    }

    .sidebar-container .nav-link i {
        margin-right: 0.75rem;
        font-size: 1.1rem;
        color: #80cbc4;
    }

    .sidebar-container .nav-link:hover {
        background-color: rgba(0, 128, 128, 0.25);
        color: #ffffff;
    }

    .sidebar-container .nav-link:hover i {
        color: #ffffff;
    }

    .sidebar-container .nav-link.active {
        background-color: #008080; /* Turquesa primario de la aplicación */
        color: #ffffff;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(0, 128, 128, 0.3);
    }

    .sidebar-container .nav-link.active i {
        color: #ffffff;
    }
</style>

<div class="d-flex flex-column h-100 sidebar-container">
    
    <!-- ==========================
         PRINCIPAL
    =========================== -->
    <div class="sidebar-heading">Panel de Control</div>
    <ul class="nav flex-column mb-2">
        <li class="nav-item">
            <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Estado de Produccion
            </a>
        </li>
    </ul>

    <!-- ==========================
         PRODUCCIÓN
    =========================== -->
    <div class="sidebar-heading">Operaciones</div>
    <ul class="nav flex-column mb-2">
        <li class="nav-item">
            <a href="{{ url('/produccion') }}" class="nav-link {{ request()->is('produccion') ? 'active' : '' }}">
                <i class="bi bi-thermometer-half"></i> Etapas de produccion
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('/control') }}" class="nav-link {{ request()->is('control') ? 'active' : '' }}">
                <i class="bi bi-sliders2"></i> Control de los dispositivos
            </a>
        </li>
    </ul>

    <!-- ==========================
         REPORTES
    =========================== -->
    <div class="sidebar-heading">Datos</div>
    <ul class="nav flex-column mb-2">
        <li class="nav-item">
            <a href="{{ url('/reportes') }}" class="nav-link {{ request()->is('reportes*') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i> Historial y Reportes
            </a>
        </li>
    </ul>

    <!-- ==========================
         SOLO ADMINISTRADOR
    =========================== -->
    @if(auth()->check() && auth()->user()->rol == 'Administrador')
        <div class="sidebar-heading">Administración</div>
        <ul class="nav flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ url('/productos') }}" class="nav-link {{ request()->is('productos*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Catálogo de Productos
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/usuarios') }}" class="nav-link {{ request()->is('usuarios*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Gestión de Usuarios
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/alertas') }}" class="nav-link {{ request()->is('alertas*') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-triangle"></i> Gestor de Alertas Térmicas
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/eventos') }}" class="nav-link {{ request()->is('eventos*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Log de Eventos (Sistema)
                </a>
            </li>
        </ul>
    @endif
    
</div>