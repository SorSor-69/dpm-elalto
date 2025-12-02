@extends('adminlte::page')

@section('title', 'Listado de Proyectos')

@section('content_header')
    <h1><i class="fas fa-project-diagram text-primary"></i> Lista de Proyectos</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header p-0 border-0">
            <div class="d-flex align-items-center px-2 py-2 nav-plomo-tabs" style="gap: 12px;">
                @if((auth()->user()->rol ?? '') !== 'TECNICO')
                <button class="btn btn-plomo-proyecto mr-2" data-toggle="modal" data-target="#modalCreate">
                    <i class="fas fa-plus"></i> Registrar Nuevo Proyecto
                </button>
                @endif
                <ul class="nav nav-tabs nav-plomo-tabs mb-0" id="proyectosTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="nuevos-tab" data-toggle="tab"
                            href="#nuevos" role="tab" aria-controls="nuevos" aria-selected="true">
                            <i class="fas fa-plus"></i> Proyectos Nuevos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="proceso-tab" data-toggle="tab"
                            href="#proceso" role="tab" aria-controls="proceso" aria-selected="false">
                            <i class="fas fa-spinner"></i> Proyectos en Proceso
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="concluidos-tab" data-toggle="tab"
                            href="#concluidos" role="tab" aria-controls="concluidos" aria-selected="false">
                            <i class="fas fa-check"></i> Proyectos Concluidos
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <div class="tab-content" id="proyectosTabsContent">
                {{-- Proyectos Nuevos --}}
                <div class="tab-pane fade show active" id="nuevos" role="tabpanel" aria-labelledby="nuevos-tab">
                    <table id="tablaProyectosNuevos" class="table table-hover table-bordered table-striped table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Acciones</th>
                                <th>📌 Nombre del Proyecto</th>
                                <th>📍 Distrito</th>
                                <th class="text-right">💰 Presupuesto</th>
                                <th class="text-center">🕒 Estado</th>
                                <th class="text-center">📅 Creación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $contador = 1; @endphp
                            @foreach($proyectos->where('estado', 'NUEVO') as $proyecto)
                                <tr>
                                    <td class="text-center align-middle">{{ $contador++ }}</td>
                                    <td class="text-center align-middle">
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                                id="dropdownMenuButton{{ $proyecto->id }}" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-cogs"></i> Acciones
                                            </button>
                                            <div class="dropdown-menu"
                                                aria-labelledby="dropdownMenuButton{{ $proyecto->id }}">
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#modalMap{{ $proyecto->id }}">
                                                    <i class="fas fa-map-marked-alt"></i> Ver ubicación
                                                </a>
                                                <a class="dropdown-item btn-edit" href="#" data-id="{{ $proyecto->id }}"
                                                    data-nombre="{{ $proyecto->nombre }}"
                                                    data-distrito="{{ $proyecto->distrito }}"
                                                    data-presupuesto="{{ $proyecto->presupuesto }}"
                                                    data-latitud="{{ $proyecto->latitud }}"
                                                    data-longitud="{{ $proyecto->longitud }}">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <a class="dropdown-item"
                                                    href="{{ route('proyectos.concluir', $proyecto->id) }}">
                                                    <i class="fas fa-check-circle"></i> Concluir
                                                </a>
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#modalDetalles{{ $proyecto->id }}">
                                                    <i class="fas fa-info-circle"></i> Más detalles
                                                </a>
                                            </div>
                                        </div>
                                        {{-- Modal Detalles --}}
                                        <div class="modal fade" id="modalDetalles{{ $proyecto->id }}" tabindex="-1" role="dialog"
                                            aria-labelledby="modalDetallesLabel{{ $proyecto->id }}" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title" id="modalDetallesLabel{{ $proyecto->id }}">
                                                            Detalles del Proyecto
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal"
                                                            aria-label="Cerrar">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <dl class="row mb-0">
                                                            <dt class="col-sm-5">Nombre del Proyecto:</dt>
                                                            <dd class="col-sm-7">{{ $proyecto->nombre }}</dd>
                                                            <dt class="col-sm-5">Distrito:</dt>
                                                            <dd class="col-sm-7">{{ $proyecto->distrito }}</dd>
                                                            <dt class="col-sm-5">Presupuesto:</dt>
                                                            <dd class="col-sm-7">
                                                                {{ number_format($proyecto->presupuesto, 2, ',', '.') }} Bs
                                                            </dd>
                                                            <dt class="col-sm-5">Descripción:</dt>
                                                            <dd class="col-sm-7">{{ $proyecto->descripcion }}</dd>
                                                            <dt class="col-sm-5">Fecha de creación:</dt>
                                                            <dd class="col-sm-7">{{ \Carbon\Carbon::parse($proyecto->fecha_creacion)->format('d/m/Y') }}</dd>
                                                            <dt class="col-sm-5">Hora de creación:</dt>
                                                            <dd class="col-sm-7">{{ \Carbon\Carbon::parse($proyecto->hora_creacion)->format('H:i') }} hrs</dd>
                                                        </dl>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">{{ $proyecto->nombre }}</td>
                                    <td class="align-middle">{{ $proyecto->distrito }}</td>
                                    <td class="align-middle text-right text-success font-weight-bold">
                                        {{ number_format($proyecto->presupuesto, 2, ',', '.') }} Bs
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-success">{{ $proyecto->estado }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ \Carbon\Carbon::parse($proyecto->fecha_creacion)->format('d/m/Y') }}
                                        <br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($proyecto->hora_creacion)->format('H:i') }} hrs</small>
                                    </td>
                                </tr>
                                @include('proyectos._modal_map', ['proyecto' => $proyecto])
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Proyectos en Proceso --}}
                <div class="tab-pane fade" id="proceso" role="tabpanel" aria-labelledby="proceso-tab">
                    <table id="tablaProyectosProceso" class="table table-hover table-bordered table-striped table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Acciones</th>
                                <th>📌 Nombre</th>
                                <th>📍 Distrito</th>
                                <th class="text-right">💰 Presupuesto</th>
                                <th class="text-center">🕒 Estado</th>
                                <th class="text-center">📅 Creación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $contador = 1; @endphp
                            @foreach($proyectos->where('estado', 'EN PROCESO') as $proyecto)
                                <tr>
                                    <td class="text-center align-middle">{{ $contador++ }}</td>
                                    <td class="text-center align-middle">
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                                id="dropdownMenuButton{{ $proyecto->id }}_proceso" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-cogs"></i> Acciones
                                            </button>
                                            <div class="dropdown-menu"
                                                aria-labelledby="dropdownMenuButton{{ $proyecto->id }}_proceso">
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#modalMap{{ $proyecto->id }}">
                                                    <i class="fas fa-map-marked-alt"></i> Ver ubicación
                                                </a>
                                                <a class="dropdown-item btn-edit" href="#" data-id="{{ $proyecto->id }}"
                                                    data-nombre="{{ $proyecto->nombre }}"
                                                    data-distrito="{{ $proyecto->distrito }}"
                                                    data-presupuesto="{{ $proyecto->presupuesto }}"
                                                    data-latitud="{{ $proyecto->latitud }}"
                                                    data-longitud="{{ $proyecto->longitud }}">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <a class="dropdown-item"
                                                    href="{{ route('proyectos.concluir', $proyecto->id) }}">
                                                    <i class="fas fa-check-circle"></i> Concluir
                                                </a>
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#modalDetalles{{ $proyecto->id }}">
                                                    <i class="fas fa-info-circle"></i> Más detalles
                                                </a>
                                            </div>
                                        </div>
                                        {{-- Modal Detalles --}}
                                        <div class="modal fade" id="modalDetalles{{ $proyecto->id }}" tabindex="-1" role="dialog"
                                            aria-labelledby="modalDetallesLabel{{ $proyecto->id }}" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title" id="modalDetallesLabel{{ $proyecto->id }}">
                                                            Detalles del Proyecto
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal"
                                                            aria-label="Cerrar">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <dl class="row mb-0">
                                                            <dt class="col-sm-5">Nombre del Proyecto:</dt>
                                                            <dd class="col-sm-7">{{ $proyecto->nombre }}</dd>
                                                            <dt class="col-sm-5">Distrito:</dt>
                                                            <dd class="col-sm-7">{{ $proyecto->distrito }}</dd>
                                                            <dt class="col-sm-5">Presupuesto:</dt>
                                                            <dd class="col-sm-7">
                                                                {{ number_format($proyecto->presupuesto, 2, ',', '.') }} Bs
                                                            </dd>
                                                            <dt class="col-sm-5">Descripción:</dt>
                                                            <dd class="col-sm-7">{{ $proyecto->descripcion }}</dd>
                                                            <dt class="col-sm-5">Fecha de creación:</dt>
                                                            <dd class="col-sm-7">{{ \Carbon\Carbon::parse($proyecto->fecha_creacion)->format('d/m/Y') }}</dd>
                                                            <dt class="col-sm-5">Hora de creación:</dt>
                                                            <dd class="col-sm-7">{{ \Carbon\Carbon::parse($proyecto->hora_creacion)->format('H:i') }} hrs</dd>
                                                        </dl>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">{{ $proyecto->nombre }}</td>
                                    <td class="align-middle">{{ $proyecto->distrito }}</td>
                                    <td class="align-middle text-right text-success font-weight-bold">
                                        {{ number_format($proyecto->presupuesto, 2, ',', '.') }} Bs
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-warning">{{ $proyecto->estado }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ \Carbon\Carbon::parse($proyecto->fecha_creacion)->format('d/m/Y') }}
                                        <br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($proyecto->hora_creacion)->format('H:i') }} hrs</small>
                                    </td>
                                </tr>
                                @include('proyectos._modal_map', ['proyecto' => $proyecto])
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Proyectos Concluidos --}}
                <div class="tab-pane fade" id="concluidos" role="tabpanel" aria-labelledby="concluidos-tab">
                    <table id="tablaProyectosConcluidos" class="table table-hover table-bordered table-striped table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Acciones</th>
                                <th>📌 Nombre</th>
                                <th>📍 Distrito</th>
                                <th class="text-right">💰 Presupuesto</th>
                                <th class="text-center">🕒 Estado</th>
                                <th class="text-center">📅 Creación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $contador = 1; @endphp
                            @foreach($proyectos->where('estado', 'CONCLUIDO') as $proyecto)
                                <tr>
                                    <td class="text-center align-middle">{{ $contador++ }}</td>
                                    <td class="text-center align-middle">
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                                id="dropdownMenuButton{{ $proyecto->id }}_concluido" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-cogs"></i> Acciones
                                            </button>
                                            <div class="dropdown-menu"
                                                aria-labelledby="dropdownMenuButton{{ $proyecto->id }}_concluido">
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#modalMap{{ $proyecto->id }}">
                                                    <i class="fas fa-map-marked-alt"></i> Ver ubicación
                                                </a>
                                                <form action="{{ route('proyectos.reactivar', $proyecto->id) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-redo"></i> Reactivar
                                                    </button>
                                                </form>
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#modalDetalles{{ $proyecto->id }}">
                                                    <i class="fas fa-info-circle"></i> Más detalles
                                                </a>
                                            </div>
                                        </div>
                                        {{-- Modal Detalles --}}
                                        <div class="modal fade" id="modalDetalles{{ $proyecto->id }}" tabindex="-1" role="dialog"
                                            aria-labelledby="modalDetallesLabel{{ $proyecto->id }}" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title" id="modalDetallesLabel{{ $proyecto->id }}">
                                                            Detalles del Proyecto
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal"
                                                            aria-label="Cerrar">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <dl class="row mb-0">
                                                            <dt class="col-sm-5">Nombre del Proyecto:</dt>
                                                            <dd class="col-sm-7">{{ $proyecto->nombre }}</dd>
                                                            <dt class="col-sm-5">Distrito:</dt>
                                                            <dd class="col-sm-7">{{ $proyecto->distrito }}</dd>
                                                            <dt class="col-sm-5">Presupuesto:</dt>
                                                            <dd class="col-sm-7">
                                                                {{ number_format($proyecto->presupuesto, 2, ',', '.') }} Bs
                                                            </dd>
                                                            <dt class="col-sm-5">Descripción:</dt>
                                                            <dd class="col-sm-7">{{ $proyecto->descripcion }}</dd>
                                                            <dt class="col-sm-5">Fecha de creación:</dt>
                                                            <dd class="col-sm-7">{{ \Carbon\Carbon::parse($proyecto->fecha_creacion)->format('d/m/Y') }}</dd>
                                                            <dt class="col-sm-5">Hora de creación:</dt>
                                                            <dd class="col-sm-7">{{ \Carbon\Carbon::parse($proyecto->hora_creacion)->format('H:i') }} hrs</dd>
                                                            @if($proyecto->fecha_conclusion)
                                                                <dt class="col-sm-5 text-success">Fecha de conclusión:</dt>
                                                                <dd class="col-sm-7 text-success">
                                                                    {{ \Carbon\Carbon::parse($proyecto->fecha_conclusion)->format('d/m/Y H:i') }} hrs
                                                                </dd>
                                                            @endif
                                                        </dl>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">{{ $proyecto->nombre }}</td>
                                    <td class="align-middle">{{ $proyecto->distrito }}</td>
                                    <td class="align-middle text-right text-success font-weight-bold">
                                        {{ number_format($proyecto->presupuesto, 2, ',', '.') }} Bs
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-secondary">{{ $proyecto->estado }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ \Carbon\Carbon::parse($proyecto->fecha_creacion)->format('d/m/Y') }}
                                        <br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($proyecto->hora_creacion)->format('H:i') }} hrs</small>
                                    </td>
                                </tr>
                                @include('proyectos._modal_map', ['proyecto' => $proyecto])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('proyectos._modal_create')
@include('proyectos._modal_edit')

@stop

@push('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <style>
        .btn-group .btn { margin-right: 2px; }
        .btn-outline-primary {
            color: #007bff;
            background-color: #fff !important;
            border-color: #007bff !important;
            font-weight: bold;
        }
        .btn-outline-primary:hover, .btn-outline-primary:focus {
            background-color: #e9f5ff !important;
            color: #0056b3 !important;
            border-color: #0056b3 !important;
        }
        .dropdown-menu .dropdown-item {
            transition: background 0.2s, color 0.2s;
        }
        .dropdown-menu .dropdown-item:hover,
        .dropdown-menu .dropdown-item:focus {
            background-color: #007bff !important;
            color: #fff !important;
        }
        .btn-info, .btn-warning, .btn-success, .btn-primary { color: #fff; }
        .btn-secondary[disabled] { opacity: 0.7; cursor: not-allowed; }
        .btn-registrar-proyecto {
            background-color:rgb(81, 203, 109) !important;
            border: 2px solid #fff !important;
            color: #fff !important; 
            font-weight: bold;
            box-shadow: 0 2px 8px #00bfff33;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        }
        .btn-registrar-proyecto:hover,
        .btn-registrar-proyecto:focus {
            background-color:rgb(16, 89, 13) !important;
            color: #fff !important;
            border: 2px solid #fff !important;
            box-shadow: 0 0 10px #009acd55;
        }
        #proyectosTabs .nav-link.nav-proyectos {
            background-color:rgb(81, 203, 109) !important;
            color: #fff !important;
            border: 2px solid #FFFFFF !important;
            font-weight: bold;
            margin-right: 4px;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        }
        #proyectosTabs .nav-link.nav-proyectos.active,
        #proyectosTabs .show>.nav-link.nav-proyectos {
            background-color: #218838 !important;
            color: #fff !important;
            border: 2px solid #145c22 !important;
            box-shadow: 0 2px 8px #21883855;
        }
        #proyectosTabs .nav-link.nav-proyectos:not(.active):hover {
            background-color: #145c22 !important;
            color: #fff !important;
            border: 2px solid #145c22 !important;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            display: inline-block;
            vertical-align: middle;
            margin-bottom: 10px;
        }
        .dataTables_wrapper .dataTables_length { float: left; }
        .dataTables_wrapper .dataTables_filter { float: right; text-align: right; }
        @media (max-width: 767.98px) {
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                float: none;
                display: block;
                text-align: left;
            }
        }
        .nav-plomo-tabs {
            background: #7a8288 !important;
            border-radius: 8px;
            border: none;
        }
        .nav-plomo-tabs .nav-link {
            background: #7a8288 !important;
            color: #fff !important;
            border: none !important;
            border-radius: 0 !important;
            font-weight: 500;
            margin-right: 6px;
            transition: background 0.2s, color 0.2s;
        }
        .nav-plomo-tabs .nav-link.active {
            background: #fff !important;
            color: #222 !important;
            border: none !important;
            font-weight: bold;
        }
        .nav-plomo-tabs .nav-link i {
            margin-right: 6px;
        }
        .btn-plomo-proyecto {
            background: #7a8288 !important;
            color: #fff !important;
            border: none !important;
            font-weight: bold;
            border-radius: 6px;
            padding: 8px 18px;
            transition: background 0.2s, color 0.2s;
        }
        .btn-plomo-proyecto:hover, .btn-plomo-proyecto:focus {
            background: #fff !important;
            color: #222 !important;
        }
    </style>
@endpush

@push('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            $('#tablaProyectosNuevos').DataTable({
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
                responsive: true,
                autoWidth: false,
                order: []
            });
            $('#tablaProyectosProceso').DataTable({
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
                responsive: true,
                autoWidth: false,
                order: []
            });
            $('#tablaProyectosConcluidos').DataTable({
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
                responsive: true,
                autoWidth: false,
                order: []
            });

            $('[data-toggle="tooltip"]').tooltip();

            // SweetAlert para concluir proyecto
            $(document).on('click', 'a[href*="proyectos/concluir"]', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                Swal.fire({
                    title: '¿Concluir proyecto?',
                    text: "Esta acción marcará el proyecto como concluido.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, concluir',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });

            // SweetAlert para reactivar proyecto
            $(document).on('submit', 'form[action*="proyectos"][action*="reactivar"]', function(e) {
                e.preventDefault();
                let form = this;
                Swal.fire({
                    title: '¿Reactivar proyecto?',
                    text: "El proyecto volverá a estar activo.",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#007bff',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, reactivar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            const puntoBase = L.latLng(-16.516522, -68.221121);
            let mapEdit, markerEdit, routingControlEdit;

            // Editar proyecto desde dropdown
            $(document).on('click', '.btn-edit', function (e) {
                e.preventDefault();
                const id = $(this).data('id');
                const nombre = $(this).data('nombre');
                const distrito = $(this).data('distrito');
                const presupuesto = $(this).data('presupuesto');
                const lat = parseFloat($(this).data('latitud'));
                const lng = parseFloat($(this).data('longitud'));

                $('#edit_id').val(id);
                $('#edit_nombre').val(nombre);
                $('#edit_distrito').val(distrito);
                // Mostrar el presupuesto actual en el campo de solo lectura y limpiar el campo de aumento
                $('#edit_presupuesto_actual').val(presupuesto);
                $('#edit_presupuesto').val('');
                $('#edit_latitud').val(lat);
                $('#edit_longitud').val(lng);

                $('#modalEdit').modal('show');

                setTimeout(() => {
                    if (mapEdit) {
                        mapEdit.remove();
                        routingControlEdit = null;
                    }

                    mapEdit = L.map('mapEditContainer').setView([lat || puntoBase.lat, lng || puntoBase.lng], 14);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(mapEdit);

                    markerEdit = L.marker([lat, lng], { draggable: true }).addTo(mapEdit);
                    markerEdit.on('dragend', function () {
                        const latlng = markerEdit.getLatLng();
                        $('#edit_latitud').val(latlng.lat.toFixed(6));
                        $('#edit_longitud').val(latlng.lng.toFixed(6));
                        dibujarRuta(latlng);
                    });

                    mapEdit.on('click', function (e) {
                        markerEdit.setLatLng(e.latlng);
                        $('#edit_latitud').val(e.latlng.lat.toFixed(6));
                        $('#edit_longitud').val(e.latlng.lng.toFixed(6));
                        dibujarRuta(e.latlng);
                    });

                    L.Control.geocoder({
                        defaultMarkGeocode: false,
                        position: 'topright'
                    }).on('markgeocode', function (e) {
                        const latlng = e.geocode.center;
                        markerEdit.setLatLng(latlng);
                        mapEdit.setView(latlng, 16);
                        $('#edit_latitud').val(latlng.lat.toFixed(6));
                        $('#edit_longitud').val(latlng.lng.toFixed(6));
                        dibujarRuta(latlng);
                    }).addTo(mapEdit);

                    dibujarRuta({ lat: lat, lng: lng });
                }, 300);
            });

            function dibujarRuta(destino) {
                if (routingControlEdit) {
                    mapEdit.removeControl(routingControlEdit);
                }

                routingControlEdit = L.Routing.control({
                    waypoints: [puntoBase, L.latLng(destino.lat, destino.lng)],
                    lineOptions: { styles: [{ color: 'blue', weight: 4 }] },
                    createMarker: () => null,
                    addWaypoints: false,
                    draggableWaypoints: false
                }).on('routesfound', function (e) {
                    const route = e.routes[0];
                    const distancia = (route.summary.totalDistance / 1000).toFixed(2);
                    const duracion = Math.round(route.summary.totalTime / 60 * 3);
                    $('#edit_distancia').text(`${distancia} km`);
                    $('#edit_duracion').text(`${duracion} min`);
                }).addTo(mapEdit);
            }
        });
    </script>
    @if(session('success'))
        <input type="hidden" id="flash-success-proyecto" value="{{ session('success') }}">
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var msg = document.getElementById('flash-success-proyecto');
                if (msg && msg.value) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('¡Éxito!', msg.value, 'success');
                    } else {
                        alert(msg.value);
                    }
                }
            });
        </script>
    @endif
@endpush