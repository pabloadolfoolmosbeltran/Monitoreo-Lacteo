<div class="d-flex flex-column h-100 sidebar-container">
    
    <div class="sidebar-heading">Panel de Control</div>
    <ul class="nav flex-column mb-2">
        <li class="nav-item">
            <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Inicio
            </a>
        </li>
    </ul>

    @php
        $operationsOpen = request()->is('produccion*', 'control*');
    @endphp
    <ul class="nav flex-column mb-2">
        <li class="nav-item">
            <button
                type="button"
                class="nav-link sidebar-group-toggle {{ $operationsOpen ? 'active-group' : '' }}"
                data-bs-toggle="collapse"
                data-bs-target="#operations-menu"
                aria-expanded="{{ $operationsOpen ? 'true' : 'false' }}"
                aria-controls="operations-menu">
                <i class="bi bi-gear-wide-connected"></i>
                <span>Operaciones</span>
                <i class="bi bi-chevron-down sidebar-chevron"></i>
            </button>
            <div id="operations-menu" class="collapse{{ $operationsOpen ? ' show' : '' }} sidebar-collapse">
                <ul class="nav flex-column sidebar-submenu">
                    <li class="nav-item">
                        <a href="{{ url('/produccion') }}" class="nav-link {{ request()->is('produccion*') ? 'active' : '' }}">
                            <i class="bi bi-thermometer-half"></i> Producción
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/control') }}" class="nav-link {{ request()->is('control*') ? 'active' : '' }}">
                            <i class="bi bi-sliders2"></i> Control ESP32
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>

    @if(auth()->check() && in_array(auth()->user()->rol, ['Administrador', 'Trabajador']))
        <div class="sidebar-heading">Catálogo</div>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a href="{{ url('/productos') }}" class="nav-link {{ request()->is('productos*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Productos
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('presentaciones.index') }}" class="nav-link {{ request()->is('presentaciones*') ? 'active' : '' }}">
                    <i class="bi bi-shop"></i> Presentaciones Comerciales
                </a>
            </li>
        </ul>

        <div class="sidebar-heading">Acopio y ventas</div>
        @php
            $inventoryOpen = request()->routeIs('ingresos-productores.*', 'inventario.*', 'inventario-general.*', 'ajustes-inventario.*', 'descartes-productos.*');
            $reportsOpen = request()->routeIs('reportes-comerciales.*', 'reportes-ingresos.*') 
                   || request()->is('reportes') 
                   || request()->is('reportes/*');
        @endphp
        <ul class="nav flex-column mb-2">
            <li class="nav-item"><a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}"><i class="bi bi-cart3"></i> Ventas</a></li>
            <li class="nav-item"><a href="{{ route('productores.index') }}" class="nav-link {{ request()->routeIs('productores.*') ? 'active' : '' }}"><i class="bi bi-person-vcard"></i> Proveedores</a></li>
            <li class="nav-item">
                <button
                    type="button"
                    class="nav-link sidebar-group-toggle {{ $inventoryOpen ? 'active-group' : '' }}"
                    data-bs-toggle="collapse"
                    data-bs-target="#inventory-menu"
                    aria-expanded="{{ $inventoryOpen ? 'true' : 'false' }}"
                    aria-controls="inventory-menu">
                    <i class="bi bi-boxes"></i>
                    <span>Inventario</span>
                    <i class="bi bi-chevron-down sidebar-chevron"></i>
                </button>
                <div id="inventory-menu" class="collapse{{ $inventoryOpen ? ' show' : '' }} sidebar-collapse">
                    <ul class="nav flex-column sidebar-submenu">
                        <li class="nav-item"><a href="{{ route('ingresos-productores.index') }}" class="nav-link {{ request()->routeIs('ingresos-productores.*') ? 'active' : '' }}"><i class="bi bi-box-arrow-in-down"></i> Entradas de Inventario</a></li>
                        <li class="nav-item"><a href="{{ route('ajustes-inventario.index') }}" class="nav-link {{ request()->routeIs('ajustes-inventario.*') ? 'active' : '' }}"><i class="bi bi-sliders"></i> Ajustes de Inventario</a></li>
                        <li class="nav-item"><a href="{{ route('descartes-productos.index') }}" class="nav-link {{ request()->routeIs('descartes-productos.*') ? 'active' : '' }}"><i class="bi bi-trash3"></i> Descartes de Productos</a></li>
                        <li class="nav-item"><a href="{{ route('inventario-general.index') }}" class="nav-link {{ request()->routeIs('inventario-general.*') ? 'active' : '' }}"><i class="bi bi-grid-3x3-gap"></i> Inventario General</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <button
                    type="button"
                    class="nav-link sidebar-group-toggle {{ $reportsOpen ? 'active-group' : '' }}"
                    data-bs-toggle="collapse"
                    data-bs-target="#commercial-reports-menu"
                    aria-expanded="{{ $reportsOpen ? 'true' : 'false' }}"
                    aria-controls="commercial-reports-menu">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Reportes</span>
                    <i class="bi bi-chevron-down sidebar-chevron"></i>
                </button>
                <div id="commercial-reports-menu" class="collapse{{ $reportsOpen ? ' show' : '' }} sidebar-collapse">
                    <ul class="nav flex-column sidebar-submenu">
                        <li class="nav-item">
                            <a href="{{ route('reportes-comerciales.index') }}" 
                            class="nav-link {{ request()->routeIs('reportes-comerciales.*') ? 'active' : '' }}">
                                <i class="bi bi-receipt"></i> Ventas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reportes-ingresos.index') }}" 
                            class="nav-link {{ request()->routeIs('reportes-ingresos.*') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-arrow-down"></i> Entradas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/reportes') }}" 
                            class="nav-link {{ (request()->is('reportes') || request()->is('reportes/*')) ? 'active' : '' }}">
                                <i class="bi bi-graph-up"></i> Reportes de Producción
                            </a>
                        </li>
                    </ul>
                </div>
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
