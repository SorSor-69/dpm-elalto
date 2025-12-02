@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('auth_header')
    <div class="text-center mb-4">
        <!-- Eliminar logo de AdminLTE, se utiliza el logo proporcionado -->
        <img src="{{ asset('images/logo1.png') }}" alt="Logo" width="120">
        <h1 class="h4 mt-3" style="color: #6f42c1;">{{ __('Restablecer la contraseña') }}</h1>
    </div>
@endsection

@section('auth_body')
    <form action="{{ route('password.email') }}" method="POST">
        @csrf

        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                placeholder="{{ __('Ingresa tu correo electrónico') }}" value="{{ old('email') }}" required autofocus>

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>

            @error('email')
                <div class="invalid-feedback d-block">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <button type="submit" class="btn btn-block" style="background-color: #6f42c1; color: white;">
            <i class="fas fa-paper-plane"></i> {{ __('Enviar enlace para restablecer la contraseña') }}
        </button>
    </form>
@endsection

@section('auth_footer')
    <div class="text-center mt-3">
        <a href="{{ route('login') }}" style="color: #6f42c1;">
            <i class="fas fa-arrow-left"></i> {{ __('Volver al inicio de sesión') }}
        </a>
    </div>
@endsection

@push('css')
    <style>
        /* Fondo lila con la imagen de fondo */
        body {
            background-color: #f3e8ff !important;
            background-image: url('{{ asset('images/fondo_inspecciones.jpg') }}');
            background-size: cover;
            background-position: center;
        }

        /* Hacer que el formulario se vea con una ligera sombra y bordes redondeados */
        .auth-page .card {
            background: rgba(255, 255, 255, 0.8); /* Fondo semi-transparente */
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Estilos para los botones */
        .btn-block {
            border-radius: 25px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn-block:hover {
            background-color: #5a3d9e; /* Color más oscuro para el hover */
        }

        /* Estilos del texto y los enlaces */
        .text-center a {
            font-weight: bold;
            text-decoration: none;
            color: #6f42c1;
        }

        .text-center a:hover {
            color: #4b2d91; /* Un tono más oscuro para el hover */
        }
    </style>
@endpush
