<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            <i class="bi bi-cpu fs-4 me-2" style="color: #0ea5e9;"></i>
            <span class="fw-bold text-dark fs-5" style="letter-spacing: -0.5px;">Sistema Inteligente de Producción Láctea</span>
        </a>

        <div class="ms-auto d-flex align-items-center">
            @auth
                <div class="text-end me-3 lh-sm">
                    <div class="fw-semibold text-dark fs-6">
                        {{ auth()->user()->name }}
                    </div>
                    <span class="text-muted" style="font-size: 0.75rem;">
                        {{ auth()->user()->rol }}
                    </span>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm d-flex align-items-center" title="Cerrar sesión">
                        <i class="bi bi-box-arrow-right me-1"></i> Salir
                    </button>
                </form>
            @endauth
        </div>
    </div>
</nav>