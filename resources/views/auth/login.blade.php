<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #f0f4f8, #d9e8f5);
            min-height: 100vh;
        }

        .login-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .login-header {
            background: linear-gradient(135deg, #0F6E56, #0C447C);
            padding: 1.75rem 1.5rem;
        }

        .login-header h4 {
            font-weight: 600;
            font-size: 1.15rem;
        }

        .login-header i {
            font-size: 1.6rem;
            opacity: 0.9;
        }

        .input-icon-wrap {
            position: relative;
        }

        .input-icon-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .input-icon-wrap input {
            padding-left: 38px;
        }

        .btn-login {
            background: #0F6E56;
            border: none;
            font-weight: 600;
            padding: 10px;
            transition: background 0.2s;
        }

        .btn-login:hover {
            background: #0c5745;
        }
    </style>
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

                        <button class="btn btn-login text-white w-100">
                            Iniciar Sesión <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
</body>
</html>