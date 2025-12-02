<div class="modal fade" id="modalCrearInspeccion" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('inspecciones.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-search-location"></i> Registrar Inspección</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <!-- Tabs principales: tipo de inspección -->
                    <div class="mb-3">
                        <ul class="nav nav-tabs nav-tabs-blue" id="tipoInspeccionTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active font-weight-bold" id="proyecto-tab" data-toggle="tab" href="#inspeccionProyecto" role="tab">Inspecciones de Proyectos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link font-weight-bold" id="corta-tab" data-toggle="tab" href="#inspeccionCorta" role="tab">Inspecciones Cortas</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content" id="tipoInspeccionTabsContent">
                        <!-- Inspecciones de Proyectos -->
                        <div class="tab-pane fade show active" id="inspeccionProyecto" role="tabpanel">
                            <label class="font-weight-bold">Seleccionar Proyecto</label>
                            <ul class="nav nav-tabs nav-tabs-blue mb-3" id="proyectosTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active font-weight-bold" id="nuevos-tab" data-toggle="tab" href="#nuevos" role="tab">Proyectos Nuevos</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link font-weight-bold" id="proceso-tab" data-toggle="tab" href="#proceso" role="tab">Proyectos en Proceso</a>
                                </li>
                            </ul>
                            <div class="tab-content" id="proyectosTabsContent">
                                <div class="tab-pane fade show active" id="nuevos" role="tabpanel">
                                    <input type="text" id="buscarProyectoNuevo" class="form-control mb-2" placeholder="Buscar...">
                                    <div style="max-height: 180px; overflow-y: auto;">
                                        <ul id="listaProyectosNuevos" class="list-group">
                                            @foreach($proyectos->where('estado', 'NUEVO')->sortBy('nombre') as $proyecto)
                                                <li class="list-group-item item-proyecto-nuevo" style="cursor:pointer;">
                                                    <label style="width:100%;cursor:pointer;margin-bottom:0;">
                                                        <input type="radio" name="proyecto_id" value="{{ $proyecto->id }}" style="margin-right:8px;">
                                                        {{ $proyecto->nombre }}
                                                    </label>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="proceso" role="tabpanel">
                                    <input type="text" id="buscarProyectoProceso" class="form-control mb-2" placeholder="Buscar...">
                                    <div style="max-height: 180px; overflow-y: auto;">
                                        <ul id="listaProyectosProceso" class="list-group">
                                            @foreach($proyectos->where('estado', 'EN PROCESO')->sortBy('nombre') as $proyecto)
                                                <li class="list-group-item item-proyecto-proceso" style="cursor:pointer;">
                                                    <label style="width:100%;cursor:pointer;margin-bottom:0;">
                                                        <input type="radio" name="proyecto_id" value="{{ $proyecto->id }}" style="margin-right:8px;">
                                                        {{ $proyecto->nombre }}
                                                    </label>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Inspecciones Cortas (proyecto manual) -->
                        <div class="tab-pane fade" id="inspeccionCorta" role="tabpanel">
                            <label class="font-weight-bold">Proyecto Manual</label>
                            <input type="text" name="proyecto_manual" id="proyecto_manual" class="form-control mb-2" placeholder="Nombre del proyecto manual">
                        </div>
                    </div>
                    <!-- Asignar funcionarios -->
                    <label class="font-weight-bold mt-3">Asignar Funcionario(s) para la Inspección</label>
                    
                    @php
                        $__user = auth()->user();
                        if (method_exists($__user, 'getRoleNames')) {
                            $__rol = $__user->getRoleNames()->first() ?? '';
                        } else {
                            $__rol = $__user->rol ?? '';
                        }
                    @endphp
                    @if($__rol === 'JEFE')
                    <!-- JEFE: solo ve Jefe(s) y Técnico(s) -->
                    <ul class="nav nav-tabs nav-tabs-blue mb-3" id="cargoTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" id="jefe-tab" data-toggle="tab" href="#jefe" role="tab">Jefe(s)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="tecnico-tab" data-toggle="tab" href="#tecnico" role="tab">Técnico(s)</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="cargoTabsContent">
                        <div class="tab-pane fade show active" id="jefe" role="tabpanel">
                            <input type="text" class="form-control mb-2" placeholder="Buscar..." id="buscarJefe">
                            <div class="row" id="listaJefe" style="max-height: 250px; overflow-y: auto;">
                                @foreach($funcionarios->where('cargo', 'JEFE')->where('activo', 1) as $funcionario)
                                    <div class="col-md-6 item-jefe">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="funcionarios[JEFE][]" value="{{ $funcionario->id }}" id="jefe_{{ $funcionario->id }}">
                                            <label class="form-check-label" for="jefe_{{ $funcionario->id }}">
                                                {{ $funcionario->nombres }} {{ $funcionario->apellidos }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tecnico" role="tabpanel">
                            <input type="text" class="form-control mb-2" placeholder="Buscar..." id="buscarTecnico">
                            <div class="row" id="listaTecnico" style="max-height: 250px; overflow-y: auto;">
                                @foreach($funcionarios->where('cargo', 'TECNICO')->where('activo', 1) as $funcionario)
                                    <div class="col-md-6 item-tecnico">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="funcionarios[TECNICO][]" value="{{ $funcionario->id }}" id="tecnico_{{ $funcionario->id }}">
                                            <label class="form-check-label" for="tecnico_{{ $funcionario->id }}">
                                                {{ $funcionario->nombres }} {{ $funcionario->apellidos }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- ADMINISTRADOR: ve todas las pestañas -->
                    <ul class="nav nav-tabs nav-tabs-blue mb-3" id="cargoTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" id="admin-tab" data-toggle="tab" href="#admin" role="tab">Administrador</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="jefe-tab" data-toggle="tab" href="#jefe" role="tab">Jefe(s)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="tecnico-tab" data-toggle="tab" href="#tecnico" role="tab">Técnico(s)</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="cargoTabsContent">
                        <div class="tab-pane fade show active" id="admin" role="tabpanel">
                            <input type="text" class="form-control mb-2" placeholder="Buscar..." id="buscarAdmin">
                            <div class="row" id="listaAdmin" style="max-height: 250px; overflow-y: auto;">
                                @foreach($funcionarios->where('cargo', 'ADMINISTRADOR')->where('activo', 1) as $funcionario)
                                    <div class="col-md-6 item-admin">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="funcionarios[ADMINISTRADOR][]" value="{{ $funcionario->id }}" id="admin_{{ $funcionario->id }}">
                                            <label class="form-check-label" for="admin_{{ $funcionario->id }}">
                                                {{ $funcionario->nombres }} {{ $funcionario->apellidos }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="tab-pane fade" id="jefe" role="tabpanel">
                            <input type="text" class="form-control mb-2" placeholder="Buscar..." id="buscarJefe">
                            <div class="row" id="listaJefe" style="max-height: 250px; overflow-y: auto;">
                                @foreach($funcionarios->where('cargo', 'JEFE')->where('activo', 1) as $funcionario)
                                    <div class="col-md-6 item-jefe">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="funcionarios[JEFE][]" value="{{ $funcionario->id }}" id="jefe_{{ $funcionario->id }}">
                                            <label class="form-check-label" for="jefe_{{ $funcionario->id }}">
                                                {{ $funcionario->nombres }} {{ $funcionario->apellidos }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tecnico" role="tabpanel">
                            <input type="text" class="form-control mb-2" placeholder="Buscar..." id="buscarTecnico">
                            <div class="row" id="listaTecnico" style="max-height: 250px; overflow-y: auto;">
                                @foreach($funcionarios->where('cargo', 'TECNICO')->where('activo', 1) as $funcionario)
                                    <div class="col-md-6 item-tecnico">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="funcionarios[TECNICO][]" value="{{ $funcionario->id }}" id="tecnico_{{ $funcionario->id }}">
                                            <label class="form-check-label" for="tecnico_{{ $funcionario->id }}">
                                                {{ $funcionario->nombres }} {{ $funcionario->apellidos }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- Actividad -->
                    <div class="form-group mt-3">
                        <label for="actividad" class="font-weight-bold">Actividad que debe realizar en la obra</label>
                        <div class="input-group">
                            <select class="form-control" id="actividad_select" style="max-width: 250px;">
                                <option value="" selected disabled>SELECCIONAR..</option>
                                <option value="Verificar avances de">VERIFICAR AVANCES DE</option>
                                <option value="Evaluar la calidad de">EVALUAR LA CALIDAD DE</option>
                                <option value="Comprobar el estado de">COMPROBAR EL ESTADO DE</option>
                                <option value="Confirmar el cumplimiento de">CONFIRMAR EL CUMPLIMIENTO DE</option>
                                <option value="Inspeccionar la instalación de">INSPECCIONAR LA INSTALACION DE</option>
                                <option value="Revisar la presencia de">REVISAR LA PRESENCIA DE</option>
                                <option value="Registrar observaciones sobre">REGISTRAR OBSERVACIONES SOBRE</option>
                            </select>
                            <input type="text" class="form-control" id="actividad_texto" placeholder="ESCRIBE AQUÍ..." maxlength="100" style="text-transform:uppercase;" oninput="this.value = this.value.toUpperCase();">
                        </div>
                        <input type="hidden" name="actividad" id="actividad_final" required>
                    </div>
                    <!-- Tiempo de inspección -->
                    <div class="form-group">
                        <label for="tiempo_inspeccion" class="font-weight-bold">Tiempo de inspección</label>
                        <div class="input-group">
                            <select class="form-control" id="tiempo_select" style="max-width: 200px;">
                                <option value="30 minutos">30 minutos</option>
                                <option value="45 minutos">45 minutos</option>
                                <option value="1 hora">1 hora</option>
                                <option value="1 hora y media">1 hora y media</option>
                                <option value="2 horas">2 horas</option>
                                <option value="+ de 2 horas">+ de 2 horas</option>
                                <option value="otro">Otro (especificar)</option>
                            </select>
                            <input type="text" class="form-control" id="tiempo_texto" placeholder="Ej: 3 horas" maxlength="30" style="display:none;">
                        </div>
                        <input type="hidden" name="tiempo_inspeccion" id="tiempo_final" required>
                    </div>
                    <!-- Observaciones eliminado -->
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Guardar</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    // Buscadores (agregar handlers solo si los elementos existen para evitar JS errors)
    (function(){
        var e;
        e = document.getElementById('buscarProyectoNuevo');
        if (e) e.addEventListener('keyup', function () {
            let filtro = this.value.toLowerCase();
            document.querySelectorAll('#listaProyectosNuevos .item-proyecto-nuevo').forEach(function (li) {
                let txt = li.innerText.toLowerCase();
                li.style.display = txt.includes(filtro) ? "" : "none";
            });
        });

        e = document.getElementById('buscarProyectoProceso');
        if (e) e.addEventListener('keyup', function () {
            let filtro = this.value.toLowerCase();
            document.querySelectorAll('#listaProyectosProceso .item-proyecto-proceso').forEach(function (li) {
                let txt = li.innerText.toLowerCase();
                li.style.display = txt.includes(filtro) ? "" : "none";
            });
        });

        e = document.getElementById('buscarAdmin');
        if (e) e.addEventListener('keyup', function () {
            let filtro = this.value.toLowerCase();
            document.querySelectorAll('#listaAdmin .item-admin').forEach(function (div) {
                let txt = div.innerText.toLowerCase();
                div.style.display = txt.includes(filtro) ? "" : "none";
            });
        });

        e = document.getElementById('buscarJefe');
        if (e) e.addEventListener('keyup', function () {
            let filtro = this.value.toLowerCase();
            document.querySelectorAll('#listaJefe .item-jefe').forEach(function (div) {
                let txt = div.innerText.toLowerCase();
                div.style.display = txt.includes(filtro) ? "" : "none";
            });
        });

        e = document.getElementById('buscarTecnico');
        if (e) e.addEventListener('keyup', function () {
            let filtro = this.value.toLowerCase();
            document.querySelectorAll('#listaTecnico .item-tecnico').forEach(function (div) {
                let txt = div.innerText.toLowerCase();
                div.style.display = txt.includes(filtro) ? "" : "none";
            });
        });
    })();

    // Actividad: Combina select y texto
    function actualizarActividadFinal() {
        let seleccion = $('#actividad_select').val();
        let texto = $('#actividad_texto').val();
        $('#actividad_final').val(seleccion + ' ' + texto);
    }
    $('#actividad_select, #actividad_texto').on('input change', actualizarActividadFinal);
    actualizarActividadFinal();

    // Tiempo de inspección: Combina select y texto
    function actualizarTiempoFinal() {
        let seleccion = $('#tiempo_select').val();
        let texto = $('#tiempo_texto').val();
        if (seleccion === 'otro') {
            $('#tiempo_final').val(texto);
        } else {
            $('#tiempo_final').val(seleccion);
        }
    }
    $('#tiempo_select').on('change', function() {
        if ($(this).val() === 'otro') {
            $('#tiempo_texto').show().focus();
        } else {
            $('#tiempo_texto').hide().val('');
        }
        actualizarTiempoFinal();
    });
    $('#tiempo_texto').on('input', actualizarTiempoFinal);
    actualizarTiempoFinal();

    // Validación dinámica para el formulario
    document.querySelector('#modalCrearInspeccion form').addEventListener('submit', function(e) {
        var tipoTab = document.querySelector('#tipoInspeccionTabsContent .tab-pane.active');
        if (tipoTab.id === 'inspeccionProyecto') {
            var proyectosTab = document.querySelector('#proyectosTabsContent .tab-pane.active');
            var radios = document.querySelectorAll('input[name="proyecto_id"]');
            var checked = false;
            radios.forEach(function(radio) { if (radio.checked) checked = true; });
            if (!checked) {
                alert('Debe seleccionar un proyecto guardado.');
                e.preventDefault();
                return false;
            }
        } else if (tipoTab.id === 'inspeccionCorta') {
            var manual = document.getElementById('proyecto_manual');
            if (!manual.value.trim()) {
                alert('Debe escribir el nombre del proyecto.');
                manual.focus();
                e.preventDefault();
                return false;
            }
        }
    });

    // Limpiar selección de proyecto y proyecto manual al cerrar/cancelar el modal
    $('#modalCrearInspeccion').on('hidden.bs.modal', function () {
        $('input[name="proyecto_id"]').prop('checked', false);
        $('#proyecto_manual').val('');
    });

    // Limpiar selección al cambiar de pestaña de tipo inspección
    $('#tipoInspeccionTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('href');
        if (target === '#inspeccionProyecto') {
            $('#proyecto_manual').val('');
        } else if (target === '#inspeccionCorta') {
            $('input[name="proyecto_id"]').prop('checked', false);
        }
    });

    // Limpiar selección al cambiar de pestaña de proyectos
    $('#proyectosTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('href');
        if (target === '#nuevos' || target === '#proceso') {
            $('input[name="proyecto_id"]').prop('checked', false);
        }
    });
</script>
@endpush

@push('css')
<style>
    .bg-plomo {
        background: #7a8288 !important;
        color: #fff !important;
    }
    .modal-header.bg-plomo,
    .modal-footer.bg-plomo {
        border: none;
    }
    .modal-content.bg-plomo input,
    .modal-content.bg-plomo select,
    .modal-content.bg-plomo textarea {
        background: #b0b6bb !important;
        color: #222 !important;
        border: 1px solid #8a8f94 !important;
    }
    .modal-content.bg-plomo .nav-tabs,
    .modal-content.bg-plomo .nav-tabs .nav-link {
        background: #7a8288 !important;
        color: #fff !important;
        border: none !important;
    }
    .modal-content.bg-plomo .nav-tabs .nav-link.active {
        background: #fff !important;
        color: #222 !important;
    }
    .modal-content.bg-plomo .list-group-item {
        background: #b0b6bb !important;
        color: #222 !important;
        border: 1px solid #8a8f94 !important;
    }
    .btn-success {
        background: #28a745 !important;
        border: none !important;
        color: #fff !important;
        font-weight: bold;
    }
    .btn-danger {
        background: #dc3545 !important;
        border: none !important;
        color: #fff !important;
        font-weight: bold;
    }
    /* Scroll personalizado */
    div::-webkit-scrollbar {
        width: 8px;
    }
    div::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    div::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    div::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
@endpush