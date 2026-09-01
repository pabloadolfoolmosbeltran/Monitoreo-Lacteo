<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    @vite(['resources/css/login.css', 'resources/js/app.js'])
</head>
<body>
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-5 col-lg-4">
            <div class="card login-card">

                <div class="login-header text-white d-flex align-items-center gap-2">
                    <i class="bi bi-droplet-fill"></i>
                    <h4 class="mb-0">Sistema Inteligente de Enfriamiento Láctea</h4>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('login.autenticar') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label small text-muted">Correo Electrónico</label>
                            <div class="input-icon-wrap">
                                <i class="bi bi-envelope"></i>
                                <input type="email" name="email" class="form-control" placeholder="usuario@correo.com" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small text-muted">Contraseña</label>
                            <div class="input-icon-wrap">
                                <i class="bi bi-lock"></i>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                        </div>

                        @if(session('error'))
                            <div class="alert alert-danger d-flex align-items-center gap-2">
                                <i class="bi bi-exclamation-circle"></i>
                                <span>{{ session('error') }}</span>
                            </div>
                        @endif

                        <button class="btn btn-login text-white w-100 mb-2">
                            Iniciar Sesión <i class="bi bi-arrow-right ms-1"></i>
                        </button>

                        <!-- Botón para volver al catálogo -->
                        <a href="http://127.0.0.1:8000/catalogo" class="btn btn-outline-catalog w-100 text-center text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i> Volver al Catálogo
                        </a>
                        
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
</body>
</html>