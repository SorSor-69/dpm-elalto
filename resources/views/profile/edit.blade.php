@extends('adminlte::page')

@section('title', 'Mi Perfil')

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger small">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li><i class="fas fa-exclamation-circle"></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container-fluid w-75">
        <div class="card shadow-lg border-0 rounded">
            <div class="card-header bg-dark text-white text-center">
                <h3 class="mb-0"><i class="fas fa-user-circle"></i> Mi Perfil </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    
                    <!-- Perfil -->
                    <div class="col-md-4">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data" id="formFotoPerfil">
                                    @csrf
                                    <div class="mb-3">
                                        <img src="{{ $user->funcionario && $user->funcionario->foto_perfil ? asset('storage/' . $user->funcionario->foto_perfil) : $user->adminlte_image() }}" class="img-thumbnail rounded-circle border border-success shadow" width="140" style="object-fit:cover;">
                                    </div>
                                    <input type="file" name="foto_perfil" id="foto_perfil" class="d-none" accept="image/*">
                                    <button type="button" class="btn btn-success btn-block font-weight-bold" onclick="document.getElementById('foto_perfil').click()">
                                        <i class="fas fa-camera"></i> Actualizar foto de perfil
                                    </button>
                                    <div id="nombreArchivo" class="text-muted small mt-2"></div>
                                    <button type="submit" class="btn btn-primary btn-block mt-2" style="display:none;" id="btnSubirFoto">
                                        <i class="fas fa-upload"></i> Subir foto
                                    </button>
                                </form>
                                <h5 class="mt-3 text-success">FUNCIONARIO:</h5>
                                <h4 class="text-dark">{{ $user->funcionario ? $user->funcionario->nombres . ' ' . $user->funcionario->apellidos : $user->name }}</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Información Personal -->
                    <div class="col-md-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-id-card"></i> Información Personal</h5>
                            </div>
                            <div class="card-body small">
                                <div class="row mb-2">
                                    <div class="col-5 font-weight-bold"><i class="fas fa-user"></i> Nombre Completo</div>
                                    <div class="col-7 text-secondary">: {{ $user->funcionario ? $user->funcionario->nombres . ' ' . $user->funcionario->apellidos : $user->name }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 font-weight-bold"><i class="fas fa-id-card"></i> Cédula</div>
                                    <div class="col-7 text-secondary">: {{ $user->funcionario ? $user->funcionario->ci : ($user->cedula ?? '') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 font-weight-bold"><i class="fas fa-venus-mars"></i> Género</div>
                                    <div class="col-7 text-secondary">: {{ $user->funcionario ? ($user->funcionario->genero == 1 ? 'Masculino' : 'Femenino') : ($user->genero == 1 ? 'Masculino' : 'Femenino') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 font-weight-bold"><i class="fas fa-envelope"></i> Email</div>
                                    <div class="col-7 text-secondary">: {{ $user->funcionario ? $user->funcionario->correo : $user->email }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 font-weight-bold"><i class="fas fa-phone"></i> Teléfono</div>
                                    <div class="col-7 text-secondary">: {{ $user->funcionario ? $user->funcionario->celular : ($user->telefono ?? '') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Cambio de Contraseña -->
                        <div class="card shadow-sm mt-3">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="fas fa-key"></i> Cambiar Contraseña (Opcional)</h5>
                            </div>
                            <div class="card-body">
                                <form id="passwordForm" method="POST" action="{{ route('profile.update') }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="font-weight-bold"><i class="fas fa-lock"></i> Nueva Contraseña</label>
                                            <input type="password" name="password" id="password" class="form-control form-control-sm mb-2" placeholder="Nueva contraseña" autocomplete="new-password">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="font-weight-bold"><i class="fas fa-lock"></i> Confirmar Contraseña</label>
                                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-sm mb-2" placeholder="Confirmar contraseña" autocomplete="new-password">
                                        </div>
                                    </div>

                                    <div class="mt-4 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary btn-sm me-2" id="confirmSave">
                                            <i class="fas fa-save me-1"></i> Guardar cambios
                                        </button>
                                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-arrow-left me-1"></i> Volver
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    @if (session('success'))
        <script>
            Swal.fire('Éxito', '{{ session('success') }}', 'success');
        </script>
    @endif
    <script>
        // Foto de perfil: mostrar nombre de archivo y auto-subir al seleccionar
        document.getElementById('foto_perfil').addEventListener('change', function() {
            const archivo = this.files[0];
            if (archivo) {
                document.getElementById('nombreArchivo').textContent = 'Seleccionado: ' + archivo.name;
                // auto-submit the photo form
                document.getElementById('btnSubirFoto').style.display = 'block';
                document.getElementById('formFotoPerfil').submit();
            } else {
                document.getElementById('nombreArchivo').textContent = '';
                document.getElementById('btnSubirFoto').style.display = 'none';
            }
        });

        // Eliminar confirmación JS para submit nativo
    </script>
@stop