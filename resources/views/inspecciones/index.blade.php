@extends('adminlte::page')

@section('title', 'Inspecciones Asignadas')

@section('content_header')
<h1><i class="fas fa-search-location text-info"></i> Inspecciones Asignadas</h1>
@stop

@section('content')
<div class="card shadow">
    <div class="card-body">

        <!-- Barra superior con tabs/botones -->
        @php
            $user = auth()->user();
            if (method_exists($user, 'getRoleNames')) {
                $rolUsuario = $user->getRoleNames()->first() ?? '';
            } else {
                $rolUsuario = $user->rol ?? '';
            }
            $miFuncionarioId = $user->funcionario->id ?? null;
        @endphp
        <div class="mb-3 nav-plomo-tabs rounded shadow-sm py-2 px-2">
            <div class="btn-group" role="group" aria-label="Inspecciones">
                @if(($rolUsuario ?? '') !== 'TECNICO')
                <button id="btnRegistrarInspeccion" class="btn btn-plomo-inspeccion font-weight-bold mr-2 d-flex align-items-center">
                    <i class="fas fa-plus-circle mr-2"></i> Registrar Inspección
                </button>
                @endif
                @if(($rolUsuario ?? '') !== 'TECNICO')
                <button id="tabProyectos" class="btn btn-plomo-inspeccion font-weight-bold mr-2 active">
                    <i class="fas fa-project-diagram mr-2"></i> Inspecciones de Proyectos
                </button>
                @endif
                @if(in_array(($rolUsuario ?? ''), ['ADMINISTRADOR','JEFE','TECNICO']))
                <button id="btnMisInspecciones" class="btn btn-plomo-inspeccion font-weight-bold mr-2" style="position:relative;">
                    <i class="fas fa-list mr-2"></i> Mis Inspecciones
                    {{-- Mostrar un icono de notificación cuando exista una nueva asignación (flash session) --}}
                    @if(session('asignacion_nueva'))
                        <span style="display:inline-block; position:relative; margin-left:8px;">
                            <i class="fas fa-bell" style="color: #fff; font-size:14px;"></i>
                            <span style="position:absolute; top:-6px; right:-6px; width:10px; height:10px; background:#dc3545; border-radius:50%; border:2px solid #fff;"></span>
                        </span>
                    @endif
                </button>
                @endif
                <button id="tabCortas" class="btn btn-plomo-inspeccion font-weight-bold">
                    <i class="fas fa-bolt mr-2"></i> Inspecciones Cortas
                </button>
            </div>
        </div>
        <!-- Sub-tabs para separar "Mis Inspecciones" y "Inspecciones de Otros" -->
        <div id="contenedorProyectos">
            <table id="tablaInspeccionesProyectos" class="table table-hover table-bordered table-striped table-sm">
                <thead class="thead-dark">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Acciones</th>
                        <th>Nombre del Proyecto</th>
                        <th class="text-center">Ubicación</th>
                        <th class="text-center">Funcionario Asignado</th>
                        <th class="text-center">Cargo</th>
                        <th class="text-center">Foto</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $miFuncionarioId = auth()->user()->funcionario->id ?? null;
                        $esAdminJefe = in_array(auth()->user()->rol ?? '', ['ADMINISTRADOR', 'JEFE']);
                        $contadorProyecto = 1;
                    @endphp
                    @foreach($inspecciones as $inspeccion)
                        @if(!empty($inspeccion->proyecto_id) && empty($inspeccion->proyecto_manual) && isset($inspeccion->proyecto))
                            @php
                                // ¿Esta inspección tiene al menos un funcionario con cargo ADMINISTRADOR?
                                $tieneAdmin = false;
                                foreach($inspeccion->funcionarios as $__ff) {
                                    if(isset($__ff->rol_en_inspeccion) && $__ff->rol_en_inspeccion === 'ADMINISTRADOR') {
                                        $tieneAdmin = true; break;
                                    }
                                }
                                // Si el usuario es JEFE, no debe ver inspecciones que incluyan ADMINISTRADOR
                                if(($rolUsuario ?? '') === 'JEFE' && $tieneAdmin) {
                                    // saltar a la siguiente inspección
                                    continue;
                                }
                            @endphp
                            @foreach($inspeccion->funcionarios as $f)
                                @php
                                    $fotos = [];
                                    if ($f->foto_llegada_obra) {
                                        $fotos = json_decode($f->foto_llegada_obra, true);
                                        if (empty($fotos)) {
                                            $fotos = [$f->foto_llegada_obra];
                                        }
                                    }
                                @endphp
                                @php
                                    // Reglas de visibilidad por rol:
                                    // TECNICO: solo ver sus propias filas
                                    // ADMIN/JEFE: NO mostrar las filas que correspondan al propio funcionario (estas van a 'Mis Inspecciones')
                                    $esMiFila = (string)($f->funcionario_id ?? '') === (string)($miFuncionarioId ?? '');
                                @endphp
                                @if(($rolUsuario === 'TECNICO' && $esMiFila) || ($rolUsuario !== 'TECNICO' && !$esMiFila))
                                <tr id="inspeccionRow{{ $inspeccion->id }}_{{ $f->id }}">
                                    <td class="text-center align-middle">{{ $contadorProyecto++ }}</td>
                                    <td class="text-center align-middle td-fotos">
                                        <button class="btn btn-celeste btn-sm px-3 font-weight-bold border border-primary" data-toggle="modal"
                                            data-target="#modalDetalle{{ $inspeccion->id }}_{{ $f->id }}" data-toggle="tooltip"
                                            title="Ver detalles de inspección" style="background: linear-gradient(90deg, #63b3ed 0%, #4299e1 100%); color: #fff; border-width:2px; transition: background 0.3s, color 0.3s;">
                                            Ver Detalles
                                        </button>
                                    </td>
                                    <td class="align-middle">{{ $inspeccion->proyecto->nombre ?? '' }}</td>
                                    <td class="text-center align-middle">
                                        <button class="btn btn-outline-info btn-sm" data-toggle="modal"
                                            data-target="#modalUbicacion{{ $inspeccion->id }}_{{ $f->id }}">
                                            <i class="fas fa-map-marker-alt"></i> Ver Ubicación
                                        </button>
                                    </td>
                                    <td class="text-center align-middle">
                                        {{ $f->funcionario->nombres ?? '' }} {{ $f->funcionario->apellidos ?? '' }}
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge 
                                                @if($f->rol_en_inspeccion == 'ADMINISTRADOR') badge-dark
                                                @elseif($f->rol_en_inspeccion == 'JEFE') badge-primary
                                                @else badge-success @endif
                                                ">
                                            {{ $f->rol_en_inspeccion }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if(!empty($fotos))
                                            <span class="text-success">Foto Subida</span>
                                        @else
                                            <span class="text-muted">No Subida</span>
                                        @endif
                                    </td>
                                </tr>
                                @include('inspecciones._modal_map', [
                                    'inspeccion' => $inspeccion,
                                    'f' => $f
                                ])
                                @include('inspecciones._modal_detalle', [
                                    'inspeccion' => $inspeccion,
                                    'f' => $f,
                                    'esMiFila' => true
                                ])
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        <div id="contenedorCortas" style="display:none;">
            <table id="tablaInspeccionesCortas" class="table table-hover table-bordered table-striped table-sm">
                <thead class="thead-dark">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Acciones</th>
                        <th>Nombre del Proyecto Manual</th>
                        <th class="text-center">Ubicación</th>
                        <th class="text-center">Funcionario Asignado</th>
                        <th class="text-center">Cargo</th>
                        <th class="text-center">Foto</th>
                    </tr>
                </thead>
                <tbody>
                    @php $contadorCorta = 1; @endphp
                    @foreach($inspecciones as $inspeccion)
                        @if(!empty($inspeccion->proyecto_manual))
                            @php
                                $tieneAdmin = false;
                                foreach($inspeccion->funcionarios as $__ff) {
                                    if(isset($__ff->rol_en_inspeccion) && $__ff->rol_en_inspeccion === 'ADMINISTRADOR') {
                                        $tieneAdmin = true; break;
                                    }
                                }
                                if(($rolUsuario ?? '') === 'JEFE' && $tieneAdmin) {
                                    continue;
                                }
                            @endphp
                            @foreach($inspeccion->funcionarios as $f)
                                @php
                                    $fotos = [];
                                    if ($f->foto_llegada_obra) {
                                        $fotos = json_decode($f->foto_llegada_obra, true);
                                        if (empty($fotos)) {
                                            $fotos = [$f->foto_llegada_obra];
                                        }
                                    }
                                    $esMiFila = (string)($f->funcionario_id ?? '') === (string)($miFuncionarioId ?? '');
                                @endphp
                                @if(($rolUsuario === 'TECNICO' && $esMiFila) || ($rolUsuario !== 'TECNICO' && !$esMiFila))
                                <tr id="inspeccionRow{{ $inspeccion->id }}_{{ $f->id }}">
                                    <td class="text-center align-middle">{{ $contadorCorta++ }}</td>
                                    <td class="text-center align-middle td-fotos">
                                        <button class="btn btn-celeste btn-sm px-3 font-weight-bold border border-primary" data-toggle="modal"
                                            data-target="#modalDetalle{{ $inspeccion->id }}_{{ $f->id }}" data-toggle="tooltip"
                                            title="Ver detalles de inspección" style="background: linear-gradient(90deg, #63b3ed 0%, #4299e1 100%); color: #fff; border-width:2px; transition: background 0.3s, color 0.3s;">
                                            Ver Detalles
                                        </button>
                                    </td>
                                    <td class="align-middle">{{ $inspeccion->proyecto_manual }}</td>
                                    <td class="text-center align-middle">
                                        <button class="btn btn-outline-info btn-sm" data-toggle="modal"
                                            data-target="#modalUbicacion{{ $inspeccion->id }}_{{ $f->id }}">
                                            <i class="fas fa-map-marker-alt"></i> Ver Ubicación
                                        </button>
                                    </td>
                                    <td class="text-center align-middle">
                                        {{ $f->funcionario->nombres ?? '' }} {{ $f->funcionario->apellidos ?? '' }}
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge @if($f->rol_en_inspeccion == 'ADMINISTRADOR') badge-dark @elseif($f->rol_en_inspeccion == 'JEFE') badge-primary @else badge-success @endif">
                                            {{ $f->rol_en_inspeccion }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if(!empty($fotos))
                                            <span class="text-success">Foto Subida</span>
                                        @else
                                            <span class="text-muted">No Subida</span>
                                        @endif
                                    </td>
                                </tr>
                                @include('inspecciones._modal_map', [
                                    'inspeccion' => $inspeccion,
                                    'f' => $f
                                ])
                                @include('inspecciones._modal_detalle', [
                                    'inspeccion' => $inspeccion,
                                    'f' => $f,
                                    'esMiFila' => true
                                ])
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

{{-- Incluye solo el modal de Inspección de Proyectos --}}
@include('inspecciones._modal_create')
<!-- Contenedor Mis Inspecciones (ADMINISTRADOR y JEFE) -->
<div id="contenedorMis" style="display:none;">
    <div class="table-responsive">
        {{-- Contenido Mis Inspecciones --}}
        <table id="tablaMisInspecciones" class="table table-hover table-bordered table-striped table-sm">
            <thead class="thead-dark">
                <tr>
                    <th class="text-center">#</th>
                    <th>Acciones</th>
                    <th>Nombre del Proyecto</th>
                    <th class="text-center">Ubicación</th>
                    <th>Funcionario</th>
                    <th class="text-center">Cargo</th>
                </tr>
            </thead>
            <tbody>
                @php $contadorMis = 1; @endphp
                @if(isset($misAsignaciones) && $misAsignaciones->count())
                    @foreach($misAsignaciones as $asig)
                        {{-- Saltar asignaciones rechazadas (activo = 0) --}}
                        @if($asig->activo == 0)
                            @continue
                        @endif
                        @php
                            $inspeccion = $asig->inspeccion;
                            $f = $asig; // alias: la asignación contiene funcionario y rol
                        @endphp
                        <tr>
                            <td class="text-center">{{ $contadorMis++ }}</td>
                            <td>
                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalDetalle{{ $inspeccion->_id ?? $inspeccion->id }}_{{ $f->id }}">Ver Detalles</button>
                            </td>
                            <td>{{ $inspeccion->proyecto->nombre ?? $inspeccion->proyecto_manual ?? 'N/A' }}</td>
                            <td class="text-center">
                                <button class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#modalUbicacion{{ $inspeccion->_id ?? $inspeccion->id }}_{{ $f->id }}">
                                    <i class="fas fa-map-marker-alt"></i> Ver Ubicación
                                </button>
                            </td>
                            <td>{{ $f->funcionario->nombres ?? '' }} {{ $f->funcionario->apellidos ?? '' }}</td>
                            <td class="text-center"><span class="badge @if($f->rol_en_inspeccion == 'ADMINISTRADOR') badge-dark @elseif($f->rol_en_inspeccion == 'JEFE') badge-primary @else badge-secondary @endif">{{ $f->rol_en_inspeccion }}</span></td>
                        </tr>
                        @include('inspecciones._modal_map', ['inspeccion' => $inspeccion, 'f' => $f])
                        @include('inspecciones._modal_detalle', ['inspeccion' => $inspeccion, 'f' => $f, 'esMiFila' => true])
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
@stop

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <style>
        .nav-plomo-tabs {
            background: #7a8288 !important;
        }
        .btn-plomo-inspeccion {
            background: #7a8288 !important;
            color: #fff !important;
            border: none !important;
            font-weight: bold;
            border-radius: 6px;
            padding: 8px 18px;
            transition: background 0.2s, color 0.2s;
        }
        .btn-plomo-inspeccion.active,
        .btn-plomo-inspeccion:active,
        .btn-plomo-inspeccion:hover,
        .btn-plomo-inspeccion:focus {
            background: #fff !important;
            color: #222 !important;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
            $('#tablaInspeccionesProyectos').DataTable({
                responsive: true,
                autoWidth: false,
                pagingType: 'simple_numbers',
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                }
            });
            $('#tablaInspeccionesCortas').DataTable({
                responsive: true,
                autoWidth: false,
                pagingType: 'simple_numbers',
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                }
            });
            $('[data-toggle="tooltip"]').tooltip();

            // REGISTRAR INSPECCIÓN button abre el modal correctamente
            $('#btnRegistrarInspeccion').on('click', function(e) {
                e.preventDefault();
                $('#modalCrearInspeccion').modal('show');
            });

            // Alternar entre tablas
            $('#tabProyectos').on('click', function() {
                $(this).addClass('active');
                $('#tabCortas').removeClass('active');
                $('#contenedorProyectos').show();
                $('#contenedorCortas').hide();
                $('#contenedorMis').hide();
            });
            $('#tabCortas').on('click', function() {
                $(this).addClass('active');
                $('#tabProyectos').removeClass('active');
                $('#btnMisInspecciones').removeClass('active');
                $('#contenedorProyectos').hide();
                $('#contenedorMis').hide();
                $('#contenedorCortas').show();
            });
            // Mostrar pestaña Mis Inspecciones (solo para ADMIN/JEFE)
            $('#btnMisInspecciones').on('click', function() {
                $('#btnMisInspecciones').addClass('active');
                $('#tabProyectos').removeClass('active');
                $('#tabCortas').removeClass('active');
                $('#contenedorProyectos').hide();
                $('#contenedorCortas').hide();
                $('#contenedorMis').show();
            });

            // Por defecto: mostrar proyectos para ADMIN/JEFE, Mis Inspecciones para TECNICO
            @if(($rolUsuario ?? '') === 'TECNICO')
                $('#btnMisInspecciones').addClass('active');
                $('#contenedorMis').show();
                $('#contenedorProyectos').hide();
                $('#contenedorCortas').hide();
            @else
                $('#tabProyectos').addClass('active');
                $('#contenedorProyectos').show();
                $('#contenedorCortas').hide();
            @endif
            // Inicializar tabla Mis Inspecciones
            if ($('#tablaMisInspecciones').length) {
                $('#tablaMisInspecciones').DataTable({
                    responsive: true,
                    autoWidth: false,
                    pagingType: 'simple_numbers',
                    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }
                });
            }

            // SweetAlert para funcionarios cuando se les asigna una inspección
            {{-- Mostrar alerta solo si la sesión indica nueva asignación y pertenece al funcionario autenticado --}}
            @php
                $funcionarioActual = auth()->user()->funcionario;
                $funcionarioId = $funcionarioActual ? (string)$funcionarioActual->id : null;
                $asignacionFuncionarioId = session('asignacion_funcionario_id') ? (string)session('asignacion_funcionario_id') : null;
                $mostrarAlerta = session('asignacion_nueva') && $funcionarioId && $asignacionFuncionarioId && ($funcionarioId === $asignacionFuncionarioId);
            @endphp
            @if($mostrarAlerta)
                Swal.fire({
                    icon: 'info',
                    title: '¡Nueva asignación!',
                    text: 'SE TE ASIGNÓ UNA NUEVA INSPECCIÓN DE PROYECTO',
                    showCancelButton: true,
                    confirmButtonText: 'Aceptar',
                    cancelButtonText: 'Rechazar',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('inspecciones.aceptar', [session('inspeccion_id'), auth()->user()->funcionario->id ?? 0]) }}',
                            type: 'POST',
                            data: {_token: '{{ csrf_token() }}'},
                            success: function(resp) {
                                Swal.fire('Inspección aceptada.');
                            },
                            error: function() {
                                Swal.fire('Error', 'No se pudo aceptar la inspección.', 'error');
                            }
                        });
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        // Confirmación simple antes de rechazar
                        Swal.fire({
                            title: '¿Estás seguro?',
                            text: 'Se rechazará esta inspección.',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, rechazar',
                            cancelButtonText: 'Cancelar',
                            allowOutsideClick: false
                        }).then((r2) => {
                            if (r2.isConfirmed) {
                                $.ajax({
                                    url: '{{ route('inspecciones.rechazar', [session('inspeccion_id'), auth()->user()->funcionario->id ?? 0]) }}',
                                    type: 'POST',
                                    data: {_token: '{{ csrf_token() }}'},
                                    success: function(resp) {
                                        Swal.fire('Inspección rechazada.', '', 'success').then(() => {
                                            location.reload();
                                        });
                                    },
                                    error: function() {
                                        Swal.fire('Error', 'No se pudo rechazar la inspección.', 'error');
                                    }
                                });
                            }
                        });
                    }
                });
            @endif
        });
    </script>
@endpush