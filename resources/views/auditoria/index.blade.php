@extends('adminlte::page')

@section('title', 'Auditoría de Accesos')

@section('content')

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2 class="mb-3">
                <i class="fas fa-shield-alt"></i> Auditoría de Accesos
            </h2>
            <p class="text-muted">Panel de monitoreo de accesos a cuentas de usuarios. Detecta cambios de dispositivo, IP y accesos sospechosos.</p>
        </div>
    </div>

    <!-- Actividades Sospechosas -->
    @if (count($suspiciousActivities) > 0)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card card-danger card-outline">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Actividades Sospechosas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>Usuario</th>
                                    <th>Funcionario</th>
                                    <th>Ultimo Acceso</th>
                                    <th>Cambios Detectados</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($suspiciousActivities as $activity)
                                <tr class="table-warning">
                                    <td>
                                        <strong>{{ $activity['user']->email }}</strong>
                                    </td>
                                    <td>
                                        @if (!empty($activity['recent_login']->user) && !empty($activity['recent_login']->user->funcionario))
                                            {{ $activity['recent_login']->user->funcionario->nombres }} {{ $activity['recent_login']->user->funcionario->apellidos }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ optional($activity['recent_login']->logged_in_at)->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        @if (!empty($activity['alerts']))
                                            <ul class="mb-0 pl-3">
                                                @foreach ($activity['alerts'] as $alert)
                                                <li><small class="text-danger"><i class="fas fa-exclamation-circle"></i> {{ $alert['message'] }}</small></li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('auditoria.acknowledge', $activity['user']->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Aceptar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i> No se han detectado actividades sospechosas.
            </div>
        </div>
    </div>
    @endif

    <!-- Accesos Recientes -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-info card-outline">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Accesos Recientes (Ultimos 50)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="loginTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Fecha/Hora</th>
                                    <th>IP</th>
                                    <th>Navegador</th>
                                    <th>Dispositivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentLogins as $login)
                                <tr>
                                    <td>
                                        <strong>
                                            @if (!empty($login->user) && !empty($login->user->funcionario))
                                                {{ $login->user->funcionario->nombres }} {{ $login->user->funcionario->apellidos }}
                                            @else
                                                {{ $login->user->name ?? 'N/A' }}
                                            @endif
                                        </strong>
                                    </td>
                                    <td>{{ $login->user->email ?? 'N/A' }}</td>
                                    <td><small>{{ optional($login->logged_in_at)->format('d/m/Y H:i:s') }}</small></td>
                                    <td><code>{{ $login->ip_address ?? 'N/A' }}</code></td>
                                    <td>{{ $login->browser ?? 'Desconocido' }}</td>
                                    <td><span class="badge badge-secondary">{{ $login->device_name ?? 'Desconocido' }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#loginTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json' },
            pageLength: 25,
            order: [[2, 'desc']]
        });
    });
</script>
@endsection
