<div class="d-flex flex-column h-100 sidebar-container">
    
    <div class="sidebar-heading">Panel de Control</div>
    <ul class="nav flex-column mb-2">
        <li class="nav-item">
            <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Estado de Produccion
            </a>
        </li>
    </ul>

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

    <div class="sidebar-heading">Datos</div>
    <ul class="nav flex-column mb-2">
        <li class="nav-item">
            <a href="{{ url('/reportes') }}" class="nav-link {{ request()->is('reportes*') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i> Historial y Reportes
            </a>
        </li>
    </ul>

    @if(auth()->check() && in_array(auth()->user()->rol, ['Administrador', 'Trabajador']))
        <div class="sidebar-heading">Catálogo</div>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a href="{{ url('/productos') }}" class="nav-link {{ request()->is('productos*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Catálogo de Productos
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('presentaciones.index') }}" class="nav-link {{ request()->is('presentaciones*') ? 'active' : '' }}">
                    <i class="bi bi-shop"></i> Presentaciones Comerciales
                </a>
            </li>
        </ul>
    @endif

    @if(auth()->check() && auth()->user()->rol == 'Administrador')
        <div class="sidebar-heading">Administración</div>
        <ul class="nav flex-column mb-auto">
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
