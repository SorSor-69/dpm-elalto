<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión | Sistema de Inspecciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Estilos de AdminLTE -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <!-- Estilos FontAwesome -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">

    <!-- Estilos personalizados -->
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                        url('{{ asset('images/fondo_inspecciones.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Source Sans Pro', sans-serif;
        }

        .login-box {
            width: 400px;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            background: rgba(255, 255, 255, 0.95);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-logo img {
            width: 100px;
            height: auto;
            animation: fadeIn 2s;
        }

        .login-box-msg {
            font-size: 18px;
            font-weight: bold;
            color: #555;
        }

        .btn-primary {
            background-color: #0069d9;
            border-color: #0062cc;
            border-radius: 8px;
            font-weight: bold;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004999;
        }

        .input-group-text {
            background-color: #0069d9;
            color: #fff;
            border: none;
        }

        @keyframes fadeIn {
            from {opacity: 0;}
            to {opacity: 1;}
        }
    </style>
</head>
<body class="hold-transition">

<div class="login-box">
    <div class="card">
        <div class="card-body login-card-body">
            <!-- Logo dentro de la tarjeta -->
            <div class="login-logo">
                <img src="{{ asset('images/logo1.png') }}" alt="Logo Sistema">
            </div>

            <p class="login-box-msg">Bienvenido al Sistema de Inspecciones</p>

            <form action="{{ route('login') }}" method="post">
                @csrf

                <!-- Campo Correo -->
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Correo electrónico" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>

                <!-- Campo Contraseña -->
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                    </div>
                </div>
            </form>



        </div>
    </div>
</div>

<!-- Scripts -->
<script src="{{ asset('vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>

</body>
</html>
