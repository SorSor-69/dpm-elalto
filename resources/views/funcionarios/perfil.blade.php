@extends('adminlte::page')

@section('title', 'Mi Perfil')
@section('content_header')
    <h1><i class="fas fa-user-circle"></i> Mi Perfil</h1>
@stop
@section('content')
<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-body text-center">
                <form action="{{ route('funcionarios.foto') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . (auth()->user()->funcionario->foto_perfil ?? 'default.jpg')) }}" class="rounded-circle" width="140" height="140" alt="Foto de perfil">
                    </div>
                    <input type="file" name="foto_perfil" accept="image/*" class="form-control mb-2" required>
                    <button type="submit" class="btn btn-success btn-block"><i class="fas fa-camera"></i> Actualizar foto de perfil</button>
                </form>
                <h4 class="mt-3 text-success">FUNCIONARIO:</h4>
                <h5>{{ auth()->user()->funcionario->nombres }} {{ auth()->user()->funcionario->apellidos }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white font-weight-bold">Información Personal</div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><b>Nombre Completo</b>: {{ auth()->user()->funcionario->nombres }} {{ auth()->user()->funcionario->apellidos }}</li>
                    <li><b>Cédula</b>: {{ auth()->user()->funcionario->ci }}</li>
                    <li><b>Género</b>: {{ auth()->user()->funcionario->genero == 1 ? 'Masculino' : 'Femenino' }}</li>
                    <li><b>Email</b>: {{ auth()->user()->funcionario->correo }}</li>
                    <li><b>Teléfono</b>: {{ auth()->user()->funcionario->celular }}</li>
                </ul>
            </div>
        </div>
        <div class="card shadow mb-4">
            <div class="card-header bg-danger text-white font-weight-bold">Cambiar Contraseña (Opcional)</div>
            <div class="card-body">
                <form action="{{ route('funcionarios.cambiarPassword') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="password">Nueva Contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="password_confirmation">Confirmar Contraseña</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Volver</a>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
