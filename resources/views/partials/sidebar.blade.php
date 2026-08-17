<div class="d-flex flex-column h-100">
    
    <!-- ==========================
         PRINCIPAL
    =========================== -->
    <div class="sidebar-heading">Panel de Control</div>
    <ul class="nav flex-column mb-2">
        <li class="nav-item">
            <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Monitor General
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
                <i class="bi bi-thermometer-half"></i> Ciclo de Producción
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('/control') }}" class="nav-link {{ request()->is('control') ? 'active' : '' }}">
                <i class="bi bi-sliders2"></i> Control Manual
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
                <i class="bi bi-graph-up"></i> Registros y Reportes
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
                    <i class="bi bi-box-seam"></i> Productos
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/usuarios') }}" class="nav-link {{ request()->is('usuarios*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Usuarios
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/alertas') }}" class="nav-link {{ request()->is('alertas*') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-triangle"></i> Gestor de Alertas
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/eventos') }}" class="nav-link {{ request()->is('eventos*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Log de Eventos
                </a>
            </li>
        </ul>
    @endif
    
</div>