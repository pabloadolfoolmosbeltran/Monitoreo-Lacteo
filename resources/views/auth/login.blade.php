<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Iniciar Sesión</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>

<body class="bg-light">

<div class="container">

    <div class="row justify-content-center mt-5">

        <div class="col-md-5">

            <div class="card shadow">

                <div class="card-header bg-primary text-white">

                    <h4 class="mb-0">

                        Sistema Inteligente de Producción Láctea

                    </h4>

                </div>

                <div class="card-body">

                    <form
                        action="{{ route('login.autenticar') }}"
                        method="POST">

                        @csrf

                        <div class="mb-3">

                            <label>

                                Correo Electrónico

                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                required>

                        </div>

                        <div class="mb-3">

                            <label>

                                Contraseña

                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                required>

                        </div>

                        @if(session('error'))

                            <div class="alert alert-danger">

                                {{ session('error') }}

                            </div>

                        @endif

                        <button
                            class="btn btn-primary w-100">

                            Iniciar Sesión

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>
