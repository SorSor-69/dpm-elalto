@extends('adminlte::page')

@section('title', 'Detalles de Accesos - ' . ($usuario->email ?? 'N/A'))

@section('content')

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-user-shield"></i> Historial de Accesos</h3>
                    <a href="{{ route('auditoria.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Email:</strong> {{ $usuario->email ?? 'N/A' }}</p>
                            <p><strong>Usuario:</strong> {{ $usuario->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            @if($funcionario)
                                <p><strong>Nombre Completo:</strong> {{ $funcionario->nombres }} {{ $funcionario->apellidos }}</p>
                                <p><strong>Cédula:</strong> {{ $funcionario->ci }}</p>
                            @else
                                <p><em class="text-muted">Sin información de funcionario registrada</em></p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Accesos -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header bg-info text-white">
                    <h3 class="mb-0"><i class="fas fa-history"></i> Accesos Registrados ({{ $logins->total() }} total)</h3>
                </div>
                <div class="card-body">
                    @if($logins->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="table-info">
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha/Hora</th>
                                        <th>IP</th>
                                        <th>Navegador</th>
                                        <th>Sistema Operativo</th>
                                        <th>Dispositivo</th>
                                        <th>User Agent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logins as $index => $login)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $login->logged_in_at ? $login->logged_in_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                            <td><code class="bg-light p-1">{{ $login->ip_address ?? 'N/A' }}</code></td>
                                            <td>{{ $login->browser ?? 'N/A' }}</td>
                                            <td>{{ $login->os ?? 'N/A' }}</td>
                                            <td><span class="badge badge-info">{{ $login->device_name ?? 'N/A' }}</span></td>
                                            <td><small class="text-muted text-truncate" style="max-width: 300px; display: block;" title="{{ $login->user_agent }}">{{ $login->user_agent ?? 'N/A' }}</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-center">
                            {{ $logins->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay registros de acceso para este usuario.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

    <!-- Informacion del Usuario -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h5 class="mb-0">Informacion del Usuario</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p>
                                <strong>Email:</strong> {{ $usuario->email }}
                            </p>
                            <p>
                                <strong>Nombre Usuario:</strong> {{ $usuario->name }}
                            </p>
                            <p>
                                <strong>Creado:</strong> {{ $usuario->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            @if ($funcionario)
                            <p>
                                <strong>Funcionario:</strong> {{ $funcionario->nombres }} {{ $funcionario->apellidos }}
                            </p>
                            <p>
                                <strong>Cargo:</strong> {{ $funcionario->cargo ?? 'N/A' }}
                            </p>
                            <p>
                                <strong>Cedula:</strong> {{ $funcionario->ci ?? 'N/A' }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-info card-outline">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Historial de Accesos
                    </h5>
                </div>
                <div class="card-body">
                    @if ($logins->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>Fecha/Hora</th>
                                    <th>IP</th>
                                    <th>Navegador</th>
                                    <th>Dispositivo</th>
                                    <th>User Agent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logins as $login)
                                <tr>
                                    <td>
                                        <strong>{{ $login->logged_in_at->format('d/m/Y H:i:s') }}</strong>
                                    </td>
                                    <td>
                                        <code>{{ $login->ip_address ?? 'N/A' }}</code>
                                    </td>
                                    <td>
                                        {{ $login->browser ?? 'Desconocido' }}
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ $login->device_name ?? 'Desconocido' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small style="word-break: break-all;">
                                            {{ $login->user_agent ?? 'N/A' }}
                                        </small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginacion -->
                    <div class="mt-3">
                        {{ $logins->links() }}
                    </div>
                    @else
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i> No hay registros de acceso para este usuario.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
