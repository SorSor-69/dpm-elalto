@extends('adminlte::page')

@section('title', 'Gestión de Funcionarios')

@section('content_header')
    <h1>REGISTRO DE FUNCIONARIOS</h1>
@endsection

@section('content')
    @if (session('success'))
        {{-- SweetAlert will show the success message in JS section below --}}
        <input type="hidden" id="flash-success" value="{{ session('success') }}">
    @endif

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let msg = '';
                @if ($errors->has('correo'))
                    msg += 'El correo ya está registrado.\n';
                @endif
                @if ($errors->has('ci'))
                    msg += 'La Cédula de Identidad ya está registrada.\n';
                @endif
                if(msg) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        html: msg.replace(/\n/g, '<br>'),
                        confirmButtonText: 'Aceptar',
                    });
                }
            });
        </script>
    @endif

    <div class="card shadow">
        <div class="card-header p-0 border-0">
            <div class="d-flex align-items-center px-2 py-2 nav-plomo-tabs" style="gap: 12px;">
                @if (auth()->user()->rol !== 'TECNICO')
                    <button class="btn btn-plomo-funcionario font-weight-bold mr-2 mb-2 mb-md-0" data-toggle="modal" data-target="#modalCreate"
                        onclick="limpiarFormulario()">
                        <i class="fas fa-plus-circle"></i> Nuevo Funcionario
                    </button>
                @endif
                <ul class="nav nav-tabs nav-plomo-tabs mb-0" id="funcionariosTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="activos-tab" data-toggle="tab" href="#activos"
                            role="tab" aria-controls="activos" aria-selected="true">
                            <i class="fas fa-user-check"></i> Funcionarios Activos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="inactivos-tab" data-toggle="tab" href="#inactivos"
                            role="tab" aria-controls="inactivos" aria-selected="false">
                            <i class="fas fa-user-times"></i> Funcionarios Inactivos
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="card-body bg-light">
            <div class="tab-content" id="funcionariosTabsContent">
                {{-- TAB FUNCIONARIOS ACTIVOS --}}
                <div class="tab-pane fade show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped table-sm" id="tabla-funcionarios-activos" style="width:100%">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Acciones</th>
                                    <th>Nombre Completo</th>
                                    <th>C.I.</th>
                                    <th>Expedido</th>
                                    <th>Correo</th>
                                    <th>Celular</th>
                                    <th>Género</th>
                                    <th>Cargo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $funcionariosActivos = $funcionarios->where('activo', true)->sortByDesc('created_at')->values();
                                @endphp
                                @foreach ($funcionariosActivos as $i => $funcionario)
                                    @if (
                                            auth()->user()->rol !== 'TECNICO' ||
                                            (auth()->user()->rol === 'TECNICO' && $funcionario->cargo === 'TECNICO')
                                        )
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton{{ $funcionario->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fas fa-cogs"></i> Acciones
                                                    </button>
                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $funcionario->id }}">
                                                        @php
                                                            $puedeEditar = auth()->user()->rol === 'ADMINISTRADOR' ||
                                                                (auth()->user()->rol === 'JEFE' && $funcionario->cargo === 'TECNICO');
                                                            $puedeDesactivar = auth()->user()->rol === 'ADMINISTRADOR' ||
                                                                (auth()->user()->rol === 'JEFE' && $funcionario->cargo === 'TECNICO');
                                                        @endphp

                                                        @if ($puedeEditar)
                                                            <a class="dropdown-item btn-edit" href="#" data-id="{{ $funcionario->id }}"
                                                                data-url="{{ route('funcionarios.update', $funcionario->id) }}">
                                                                <i class="fas fa-edit text-info"></i> Editar
                                                            </a>
                                                        @else
                                                            <span class="dropdown-item text-muted"><i class="fas fa-edit"></i> Editar</span>
                                                        @endif

                                                        @if ($puedeDesactivar)
                                                            <form method="POST"
                                                                action="{{ route('funcionarios.desactivar', $funcionario->id) }}"
                                                                class="form-desactivar d-inline">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fas fa-toggle-off text-danger"></i> Desactivar
                                                                </button>
                                                            </form>
                                                        @else
                                                            <span class="dropdown-item text-muted"><i class="fas fa-toggle-off"></i> Desactivar</span>
                                                        @endif

                                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modalDetalles{{ $funcionario->id }}">
                                                            <i class="fas fa-info-circle text-success"></i> Más detalles
                                                        </a>
                                                    </div>
                                                </div>
                                                {{-- Modal Detalles --}}
                                                <div class="modal fade" id="modalDetalles{{ $funcionario->id }}" tabindex="-1" role="dialog" aria-labelledby="modalDetallesLabel{{ $funcionario->id }}" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title" id="modalDetallesLabel{{ $funcionario->id }}">
                                                                    Detalles del Funcionario
                                                                </h5>
                                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <dl class="row mb-0">
                                                                    <dt class="col-sm-5">Nombre Completo:</dt>
                                                                    <dd class="col-sm-7">{{ $funcionario->nombres }} {{ $funcionario->apellidos }}</dd>
                                                                    <dt class="col-sm-5">C.I.:</dt>
                                                                    <dd class="col-sm-7">{{ $funcionario->ci }}@if($funcionario->complemento && trim($funcionario->complemento) !== '')-{{ $funcionario->complemento }}@endif</dd>
                                                                    <dt class="col-sm-5">Expedido:</dt>
                                                                    <dd class="col-sm-7">{{ $funcionario->expedido }}</dd>
                                                                    <dt class="col-sm-5">Correo:</dt>
                                                                    <dd class="col-sm-7">{{ $funcionario->correo }}</dd>
                                                                    <dt class="col-sm-5">Celular:</dt>
                                                                    <dd class="col-sm-7">{{ $funcionario->celular }}</dd>
                                                                    <dt class="col-sm-5">Género:</dt>
                                                                    <dd class="col-sm-7">
                                                                        @if($funcionario->genero == 1)
                                                                            Masculino
                                                                        @elseif($funcionario->genero == 2)
                                                                            Femenino
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </dd>
                                                                    <dt class="col-sm-5">Cargo:</dt>
                                                                    <dd class="col-sm-7">{{ $funcionario->cargo }}</dd>
                                                                    <dt class="col-sm-5">Fecha de Nacimiento:</dt>
                                                                    <dd class="col-sm-7">{{ $funcionario->fecha_nacimiento }}</dd>
                                                                    <dt class="col-sm-5">Fecha de Registro:</dt>
                                                                    <dd class="col-sm-7">{{ $funcionario->fecha_registro }}</dd>
                                                                </dl>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $funcionario->nombres }} {{ $funcionario->apellidos }}</td>
                                            <td>
                                                {{ $funcionario->ci }}@if($funcionario->complemento && trim($funcionario->complemento) !== '')-{{ $funcionario->complemento }}@endif
                                            </td>
                                            <td>{{ $funcionario->expedido }}</td>
                                            <td>{{ $funcionario->correo }}</td>
                                            <td>{{ $funcionario->celular }}</td>
                                            <td>
                                                @if($funcionario->genero == 1)
                                                    Masculino
                                                @elseif($funcionario->genero == 2)
                                                    Femenino
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ ['ADMINISTRADOR' => 'warning', 'JEFE' => 'danger', 'TECNICO' => 'success'][$funcionario->cargo] ?? 'secondary' }}">
                                                    {{ $funcionario->cargo }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">Activo</span>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- TAB FUNCIONARIOS INACTIVOS --}}
                <div class="tab-pane fade" id="inactivos" role="tabpanel" aria-labelledby="inactivos-tab">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped table-sm" id="tabla-funcionarios-inactivos" style="width:100%">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Acciones</th>
                                    <th>Nombre Completo</th>
                                    <th>C.I.</th>
                                    <th>Expedido</th>
                                    <th>Correo</th>
                                    <th>Celular</th>
                                    <th>Género</th>
                                    <th>Cargo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $funcionariosInactivos = $funcionarios->where('activo', false)->sortByDesc('created_at')->values();
                                @endphp
                                @foreach ($funcionariosInactivos as $i => $funcionario)
                                    @if (
                                        auth()->user()->rol !== 'TECNICO' ||
                                        (auth()->user()->rol === 'TECNICO' && $funcionario->cargo === 'TECNICO')
                                    )
                                    <tr style="opacity:0.7;">
                                        <td>{{ $i + 1 }}</td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button class="btn btn-outline-primary btn-sm dropdown-toggle"
                                                        type="button"
                                                        id="dropdownMenuButtonInac{{ $funcionario->id }}"
                                                        data-toggle="dropdown"
                                                        data-display="static"
                                                        aria-haspopup="true"
                                                        aria-expanded="false">
                                                    <i class="fas fa-cogs"></i> Acciones
                                                </button>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButtonInac{{ $funcionario->id }}">
                                                    <span class="dropdown-item text-muted" style="pointer-events:none;">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </span>
                                                    @php
                                                        $puedeReactivar = auth()->user()->rol === 'ADMINISTRADOR' ||
                                                            (auth()->user()->rol === 'JEFE' && $funcionario->cargo === 'TECNICO');
                                                    @endphp
                                                    @if ($puedeReactivar)
                                                        <form method="POST" action="{{ route('funcionarios.reactivar', $funcionario->id) }}"
                                                            class="form-reactivar d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fas fa-toggle-on text-success"></i> Reactivar
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="dropdown-item text-muted"><i class="fas fa-toggle-on"></i> Reactivar</span>
                                                    @endif

                                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modalDetalles{{ $funcionario->id }}">
                                                        <i class="fas fa-info-circle text-primary"></i> Más detalles
                                                    </a>
                                                </div>
                                            </div>
                                            {{-- Modal Detalles --}}
                                            <div class="modal fade" id="modalDetalles{{ $funcionario->id }}" tabindex="-1" role="dialog" aria-labelledby="modalDetallesLabel{{ $funcionario->id }}" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-secondary text-white">
                                                            <h5 class="modal-title" id="modalDetallesLabel{{ $funcionario->id }}">
                                                                Detalles del Funcionario
                                                            </h5>
                                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <dl class="row mb-0">
                                                                <dt class="col-sm-5">Nombre Completo:</dt>
                                                                <dd class="col-sm-7">{{ $funcionario->nombres }} {{ $funcionario->apellidos }}</dd>
                                                                <dt class="col-sm-5">C.I.:</dt>
                                                                <dd class="col-sm-7">{{ $funcionario->ci }}@if($funcionario->complemento && trim($funcionario->complemento) !== '')-{{ $funcionario->complemento }}@endif</dd>
                                                                <dt class="col-sm-5">Expedido:</dt>
                                                                <dd class="col-sm-7">{{ $funcionario->expedido }}</dd>
                                                                <dt class="col-sm-5">Correo:</dt>
                                                                <dd class="col-sm-7">{{ $funcionario->correo }}</dd>
                                                                <dt class="col-sm-5">Celular:</dt>
                                                                <dd class="col-sm-7">{{ $funcionario->celular }}</dd>
                                                                <dt class="col-sm-5">Género:</dt>
                                                                <dd class="col-sm-7">
                                                                    @if($funcionario->genero == 1)
                                                                        Masculino
                                                                    @elseif($funcionario->genero == 2)
                                                                        Femenino
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </dd>
                                                                <dt class="col-sm-5">Cargo:</dt>
                                                                <dd class="col-sm-7">{{ $funcionario->cargo }}</dd>
                                                                <dt class="col-sm-5">Fecha de Nacimiento:</dt>
                                                                <dd class="col-sm-7">{{ $funcionario->fecha_nacimiento }}</dd>
                                                                <dt class="col-sm-5">Fecha de Registro:</dt>
                                                                <dd class="col-sm-7">{{ $funcionario->fecha_registro }}</dd>
                                                            </dl>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $funcionario->nombres }} {{ $funcionario->apellidos }}</td>
                                        <td>
                                            {{ $funcionario->ci }}@if($funcionario->complemento && trim($funcionario->complemento) !== '')-{{ $funcionario->complemento }}@endif
                                        </td>
                                        <td>{{ $funcionario->expedido }}</td>
                                        <td>{{ $funcionario->correo }}</td>
                                        <td>{{ $funcionario->celular }}</td>
                                        <td>
                                            @if($funcionario->genero == 1)
                                                Masculino
                                            @elseif($funcionario->genero == 2)
                                                Femenino
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ ['ADMINISTRADOR' => 'warning', 'JEFE' => 'danger', 'TECNICO' => 'success'][$funcionario->cargo] ?? 'secondary' }}">
                                                {{ $funcionario->cargo }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">Desactivado</span>
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de creación --}}
    @include('funcionarios._modal_create')

    {{-- Modal de edición --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <form method="POST" id="formEditFuncionario">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Editar Funcionario</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body" id="edit-form-content"></div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css"/>
    <style>
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
        .btn-plomo-funcionario {
            background: #7a8288 !important;
            color: #fff !important;
            border: none !important;
            font-weight: bold;
            border-radius: 6px;
            padding: 8px 18px;
            transition: background 0.2s, color 0.2s;
        }
        .btn-plomo-funcionario:hover, .btn-plomo-funcionario:focus {
            background: #fff !important;
            color: #222 !important;
        }
        /* Botón Registrar Nuevo Funcionario: celeste */
        .btn-success {
            color: #fff;
            font-weight: bold;
            background-color:rgb(89, 186, 255) !important;
            border-color:rgb(8, 15, 22) !important;
        }
        .btn-success:hover, .btn-success:focus {
            background-color:rgb(33, 50, 136) !important;
            border-color:rgb(8, 15, 22) !important;
            color: #fff !important;
            box-shadow: 0 0 10px #28a74555;
        }
        /* Botón de acciones (dropdown): blanco con borde azul */
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
        /* Opciones del dropdown: azul al pasar mouse */
        .dropdown-menu .dropdown-item {
            transition: background 0.2s, color 0.2s;
        }
        .dropdown-menu .dropdown-item:hover,
        .dropdown-menu .dropdown-item:focus {
            background-color: #007bff !important;
            color: #fff !important;
        }
        /* Pestañas: activos azul, inactivos gris */
        #funcionariosTabs .nav-link.nav-funcionarios {
            background-color:rgb(89, 186, 255) !important;
            color:rgb(8, 15, 22) !important;
            border: 2px solid #007bff !important;
            font-weight: bold;
            margin-right: 4px;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        }
        #funcionariosTabs .nav-link.nav-funcionarios.active,
        #funcionariosTabs .show>.nav-link.nav-funcionarios {
            background-color: #007bff !important;
            color: #fff !important;
            border: 2px solid rgb(8, 15, 22) !important;
            box-shadow: 0 2px 8px #007bff55;
        }
        #funcionariosTabs .nav-link.nav-funcionarios:not(.active):hover {
            background-color: #e9f5ff !important;
            color: #0056b3 !important;
            border: 2px solid rgb(8, 15, 22) !important;
        }
        /* Badges */
        .badge-success {
            background-color: #28a745 !important;
        }
        .badge-secondary {
            background-color: #6c757d !important;
        }
        .badge-warning {
            background-color: #ffc107 !important;
            color: #212529;
        }
        .badge-danger {
            background-color: #dc3545 !important;
        }
        .badge-info {
            background-color: #87CEEB !important;
        }
        /* DataTables controles alineados */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            display: inline-block;
            vertical-align: middle;
            margin-bottom: 10px;
        }
        .dataTables_wrapper .dataTables_length {
            float: left;
        }
        .dataTables_wrapper .dataTables_filter {
            float: right;
            text-align: right;
        }
        @media (max-width: 767.98px) {
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                float: none;
                display: block;
                text-align: left;
            }
        }
    </style>
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
            $('#tabla-funcionarios-activos').DataTable({
                dom: 'lfrtip',
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100]
            });
            $('#tabla-funcionarios-inactivos').DataTable({
                dom: 'lfrtip',
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100]
            });

            $('[data-toggle="tooltip"]').tooltip();

            // Botón editar
            $('.btn-edit').click(function () {
                const id = $(this).data('id');
                const url = `/funcionarios/${id}/edit`;

                $.get(url, function (html) {
                    $('#edit-form-content').html(html);
                    $('#formEditFuncionario').attr('action', `/funcionarios/${id}`);
                    $('#modalEdit').modal('show');
                });
            });

            // Confirmación para desactivar con SweetAlert2
            $('.form-desactivar').on('submit', function (e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Este funcionario será desactivado.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, desactivarlo',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // Confirmación para reactivar con SweetAlert2
            $('.form-reactivar').on('submit', function (e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: '¿Reactivar funcionario?',
                    text: "Este funcionario será reactivado.",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, reactivarlo',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // Mostrar SweetAlert si existe un mensaje de éxito en sesión
            var flash = document.getElementById('flash-success');
            if (flash && flash.value) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('¡Éxito!', flash.value, 'success');
                } else {
                    alert(flash.value);
                }
            }
        });

        function limpiarFormulario() {
            document.querySelector('#formCreateFuncionario').reset();
        }
    </script>
@endsection