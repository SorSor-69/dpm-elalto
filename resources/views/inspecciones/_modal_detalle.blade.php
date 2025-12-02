<div class="modal fade" id="modalDetalle{{ $inspeccion->id }}_{{ $f->id }}" tabindex="-1" role="dialog"
    aria-labelledby="modalDetalleLabel{{ $inspeccion->id }}_{{ $f->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" data-row-id="inspeccionRow{{ $inspeccion->id }}_{{ $f->id }}">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalDetalleLabel{{ $inspeccion->id }}_{{ $f->id }}">
                    <i class="fas fa-search-location"></i> Detalle de Inspección
                    @if(!empty($inspeccion->proyecto_id) && isset($inspeccion->proyecto))
                        #{{ $inspeccion->id }}
                    @endif
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="detalleContenido{{ $inspeccion->id }}_{{ $f->id }}">

                <ul class="mb-2">
                    <li><strong>Proyecto:</strong> {{ $inspeccion->proyecto->nombre ?? $inspeccion->proyecto_manual ?? '-' }}</li>
                    @if(!empty($inspeccion->proyecto_id) && isset($inspeccion->proyecto))
                        <li><strong>Descripción:</strong> {{ $inspeccion->proyecto->descripcion ?? '-' }}</li>
                        <li><strong>Distrito:</strong> {{ $inspeccion->proyecto->distrito ?? '-' }}</li>
                        <li><strong>Presupuesto:</strong> {{ $inspeccion->proyecto->presupuesto ? number_format($inspeccion->proyecto->presupuesto, 2, ',', '.') . ' Bs' : '-' }}</li>
                    @endif
                </ul>
                <ul class="mb-2">
                    <li><strong>Actividad a Realizar:</strong> {{ $inspeccion->actividad ?? '-' }}</li>
                    <li><strong>Tiempo de Inspección:</strong> {{ $inspeccion->tiempo_inspeccion ?? '-' }}</li>
                    <li><strong>Creada el:</strong> {{ $inspeccion->created_at ? $inspeccion->created_at->format('d/m/Y H:i') : '-' }}</li>
                </ul>
                <script>
                function iniciarCuentaRegresiva_{{ $inspeccion->id }}_{{ $f->id }}() {
                    function parseTiempo(tiempo) {
                        if (!tiempo) return 0;
                        tiempo = tiempo.toLowerCase();
                        if (tiempo.includes('minuto')) {
                            let m = tiempo.match(/(\d+)/);
                            return m ? parseInt(m[1]) * 60 : 0;
                        }
                        if (tiempo.includes('hora')) {
                            let m = tiempo.match(/(\d+)/);
                            let minutos = m ? parseInt(m[1]) * 60 : 0;
                            if (tiempo.includes('media')) minutos += 30;
                            if (tiempo.includes('+')) minutos += 60;
                            return minutos * 60;
                        }
                        return 0;
                    }
                    let tiempoAsignado = parseTiempo(@json($inspeccion->tiempo_inspeccion));
                    let salidaGamea = @json($f->salida_gamea ?? null);
                    let inicio = salidaGamea ? salidaGamea : null;
                    var elCrono = document.getElementById('cronometro{{ $inspeccion->id }}_{{ $f->id }}');
                    var elDot = document.getElementById('estadoDot{{ $inspeccion->id }}_{{ $f->id }}');
                    if (tiempoAsignado > 0 && inicio) {
                        let inicioDate = new Date(inicio.replace(' ', 'T'));
                        function actualizarTiempo() {
                            let ahora = new Date();
                            let transcurrido = Math.floor((ahora - inicioDate) / 1000);
                            let restante = tiempoAsignado - transcurrido;
                            let el = document.getElementById('tiempoRestante{{ $inspeccion->id }}_{{ $f->id }}');
                            if (restante > 0) {
                                let min = Math.floor(restante / 60);
                                let seg = restante % 60;
                                el.textContent = `Te quedan ${min} min ${seg} seg para completar la inspección.`;
                                el.style.color = '#007bff';
                                if (elCrono) elCrono.textContent = `${min}m ${seg}s`;
                                if (elDot) { elDot.style.background = '#007bff'; elDot.style.display = 'inline-block'; }
                            } else {
                                let retraso = Math.abs(restante);
                                let min = Math.floor(retraso / 60);
                                let seg = retraso % 60;
                                el.textContent = `Llevas un retraso de ${min} min ${seg} seg.`;
                                el.style.color = '#dc3545';
                                if (elCrono) elCrono.textContent = `+${min}m ${seg}s`;
                                if (elDot) { elDot.style.background = '#dc3545'; elDot.style.display = 'inline-block'; }
                            }
                        }
                        actualizarTiempo();
                        window['intervalCuentaRegresiva_{{ $inspeccion->id }}_{{ $f->id }}'] = setInterval(actualizarTiempo, 1000);
                    } else if (tiempoAsignado > 0 && !inicio) {
                        // No ha iniciado aún: mostrar tiempo asignado y punto azul
                        var minAsign = Math.floor(tiempoAsignado / 60);
                        if (elCrono) elCrono.textContent = `${minAsign} min asignados`;
                        if (elDot) { elDot.style.background = '#6c757d'; elDot.style.display = 'inline-block'; }
                    } else {
                        if (elDot) elDot.style.display = 'none';
                    }
                }
                $('#modalDetalle{{ $inspeccion->id }}_{{ $f->id }}').on('shown.bs.modal', function () {
                    iniciarCuentaRegresiva_{{ $inspeccion->id }}_{{ $f->id }}();
                });
                $('#modalDetalle{{ $inspeccion->id }}_{{ $f->id }}').on('hidden.bs.modal', function () {
                    if (window['intervalCuentaRegresiva_{{ $inspeccion->id }}_{{ $f->id }}']) {
                        clearInterval(window['intervalCuentaRegresiva_{{ $inspeccion->id }}_{{ $f->id }}']);
                    }
                });
                </script>

                <div class="mb-3">
                    <h5 class="mb-1">Detalles de la Inspección
                        <span id="estadoDot{{ $inspeccion->id }}_{{ $f->id }}" style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#007bff;margin-left:8px;vertical-align:middle;"></span>
                        <span id="cronometro{{ $inspeccion->id }}_{{ $f->id }}" style="margin-left:8px;font-weight:600;color:#007bff;"></span>
                    </h5>
                    <ul class="list-group">
                        @if(!empty($f->detalle_inspeccion) && is_array($f->detalle_inspeccion))
                            @foreach($f->detalle_inspeccion as $detalle)
                                <li class="list-group-item py-1">
                                    <span class="text-uppercase">
                                        {{ is_array($detalle) ? (mb_strtoupper($detalle['detalle'] ?? '', 'UTF-8')) : mb_strtoupper($detalle, 'UTF-8') }}
                                    </span>
                                    @if(is_array($detalle) && isset($detalle['fecha']))
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($detalle['fecha'])->format('d/m/Y H:i') }}</small>
                                    @endif
                                </li>
                            @endforeach
                        @else
                            <li class="list-group-item py-1 text-muted">Sin detalles registrados.</li>
                        @endif
                    </ul>
                </div>

                @if($esMiFila)
                <form class="mb-3 form-agregar-detalle" method="POST" action="{{ route('inspecciones.agregarDetalle', [$inspeccion->id, $f->funcionario_id]) }}" autocomplete="off">
                    @csrf
                    <div class="input-group">
                        <input type="text" class="form-control detalle-texto" name="detalle_inspeccion" placeholder="Escribe el detalle aquí..." maxlength="200" style="text-transform:uppercase;" required>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-success">Guardar</button>
                        </div>
                    </div>
                </form>
                <script>
                // Validación simple: no permitir vacío y forzar mayúsculas
                $(document).off('submit.formAgregarDetalle').on('submit.formAgregarDetalle', '.form-agregar-detalle', function(e) {
                    var $form = $(this);
                    var texto = $form.find('.detalle-texto').val().toUpperCase();
                    $form.find('.detalle-texto').val(texto);
                    if(!texto) {
                        e.preventDefault();
                        Swal.fire('Debe escribir el detalle.');
                        return false;
                    }
                });
                // SweetAlert para nueva asignación
                {{-- Mostrar alerta solo si la sesión indica nueva asignación y pertenece al funcionario de este modal --}}
                @php
                    $asigFuncionarioId = session('asignacion_funcionario_id') ? (string)session('asignacion_funcionario_id') : null;
                    $fFuncionarioId = $f->funcionario_id ? (string)$f->funcionario_id : null;
                    $mostrarAlertaDetalle = session('asignacion_nueva') && $asigFuncionarioId && $fFuncionarioId && ($asigFuncionarioId === $fFuncionarioId);
                @endphp
                @if($mostrarAlertaDetalle)
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
                            url: '{{ route('inspecciones.aceptar', [$inspeccion->id, $f->funcionario_id]) }}',
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
                                    url: '{{ route('inspecciones.rechazar', [$inspeccion->id, $f->funcionario_id]) }}',
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
                </script>
                @endif

                <hr>
                <h5>Personal Asignado:</h5>
                <ul>
                    <li>
                        <strong>
                            <span class="badge 
                                @if($f->rol_en_inspeccion == 'ADMINISTRADOR') badge-dark
                                @elseif($f->rol_en_inspeccion == 'JEFE') badge-primary
                                @else badge-success @endif
                            ">
                                {{ $f->rol_en_inspeccion }}
                            </span>
                        </strong>
                        {{ $f->funcionario->nombres ?? '' }} {{ $f->funcionario->apellidos ?? '' }}
                        @if($f->hora_salida_gamea)
                            <br><small>Salida del GAMEA: {{ $f->hora_salida_gamea }}</small>
                        @endif
                        @if($f->hora_llegada_obra)
                            <br><small>Llegada a la Obra: {{ $f->hora_llegada_obra }}</small>
                        @endif
                        @if($f->hora_salida_obra)
                            <br><small>Salida de la Obra: {{ $f->hora_salida_obra }}</small>
                        @endif
                        @if($f->hora_llegada_gamea)
                            <br><small>Llegada al GAMEA: {{ $f->hora_llegada_gamea }}</small>
                        @endif
                    </li>
                </ul>

                @if($esMiFila)
                    {{-- Etapas de flujo para el funcionario asignado --}}
                    @if(is_null($f->hora_salida_gamea))
                        <form class="form-flujo" action="{{ route('inspecciones.aceptar', [$inspeccion->id, $f->funcionario_id]) }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="latitud" class="input-latitud">
                            <input type="hidden" name="longitud" class="input-longitud">
                            <button id="btnSalidaGamea{{ $inspeccion->id }}_{{ $f->id }}" type="submit" class="btn btn-success btn-sm btn-salida-gamea" @if(empty($f->latitud_actual) || empty($f->longitud_actual)) disabled @endif>
                                <i class="fas fa-sign-out-alt"></i> Salida GAMEA
                            </button>
                        </form>
                        @if(empty($f->latitud_actual) || empty($f->longitud_actual))
                            <small class="text-danger d-block">Debes registrar tu ubicación antes de salir del GAMEA.</small>
                        @endif
                    @elseif(is_null($f->hora_llegada_obra))
                        <form class="form-flujo" action="{{ route('inspecciones.marcarLlegada', [$inspeccion->id, $f->funcionario_id]) }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="latitud" class="input-latitud">
                            <input type="hidden" name="longitud" class="input-longitud">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-flag-checkered"></i> Llegada Obra
                            </button>
                        </form>
                    @else
                        {{-- Ya llegó a la obra: mostrar subida de fotos (si <5) y siempre el botón Salida Obra si no está marcado --}}
                        @if(is_null($f->hora_salida_obra))
                            @if(empty($fotos) || count($fotos) < 5)
                                <form class="form-subir-fotos" action="{{ route('inspecciones.subirFotos', [$inspeccion->id, $f->funcionario_id]) }}" method="POST" enctype="multipart/form-data" style="display:inline;">
                                    @csrf
                                    <input type="file" name="fotos_llegada_obra[]" accept="image/*" capture="environment" multiple required>
                                    <input type="hidden" name="latitud" class="input-latitud">
                                    <input type="hidden" name="longitud" class="input-longitud">
                                    <button type="submit" class="btn btn-info btn-sm">
                                        <i class="fas fa-camera"></i> Subir Foto(s)
                                    </button>
                                </form>
                            @endif

                            <form class="form-flujo" action="{{ route('inspecciones.marcarSalida', [$inspeccion->id, $f->funcionario_id]) }}" method="POST" style="display:inline;">
                                @csrf
                                <input type="hidden" name="latitud" class="input-latitud">
                                <input type="hidden" name="longitud" class="input-longitud">
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="fas fa-sign-out-alt"></i> Salida Obra
                                </button>
                            </form>
                        @elseif(is_null($f->hora_llegada_gamea))
                            <form class="form-flujo" action="{{ route('inspecciones.marcarLlegadaGamea', [$inspeccion->id, $f->funcionario_id]) }}" method="POST" style="display:inline;">
                                @csrf
                                <input type="hidden" name="latitud" class="input-latitud">
                                <input type="hidden" name="longitud" class="input-longitud">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-home"></i> Llegada GAMEA
                                </button>
                            </form>
                        @else
                            <span class="text-success"><i class="fas fa-check-circle"></i> Finalizada</span>
                        @endif
                    @endif
                @endif
                <hr>

                @if($esMiFila && (is_null($f->hora_salida_gamea)))
                    {{-- Botón de subir ubicación removido (se realiza desde el modal de mapa). Mantener campos ocultos. --}}
                    <input type="hidden" name="inspeccion_id" value="{{ $inspeccion->id }}">
                    <input type="hidden" name="funcionario_id" value="{{ $f->funcionario_id }}">
                    <input type="hidden" name="latitud" id="latitudActual{{ $inspeccion->id }}_{{ $f->id }}">
                    <input type="hidden" name="longitud" id="longitudActual{{ $inspeccion->id }}_{{ $f->id }}">
                @endif

                <h5>Foto(s) subida(s):</h5>
                @php
                    $fotos = [];
                    if ($f->foto_llegada_obra) {
                        $fotos = json_decode($f->foto_llegada_obra, true);
                        if (empty($fotos)) {
                            $fotos = [$f->foto_llegada_obra];
                        }
                    }
                @endphp

                @if(!empty($fotos))
                    <div class="d-flex flex-wrap">
                    @foreach($fotos as $i => $foto)
                        <div class="position-relative mr-2 mb-2">
                            <img src="{{ asset('storage/' . $foto) }}" class="img-fluid rounded" alt="Foto llegada obra" width="120">
                            @if($esMiFila && is_null($f->hora_salida_obra))
                                <button type="button" class="btn btn-sm btn-danger btn-eliminar-foto position-absolute" style="top:4px; right:4px;" data-index="{{ $i }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    @endforeach
                    </div>
                @else
                    <span class="text-muted">No subida</span>
                @endif
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Ensure AJAX includes CSRF header
if (typeof $ !== 'undefined' && $.ajaxSetup) {
    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (tokenMeta) {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': tokenMeta.getAttribute('content') } });
    }
}
let enviandoFlujo = false;

function enviarAjax(form, $btn) {
    var formData = new FormData(form);
    console.debug('enviarAjax: url=', form.action, 'method=', form.method, 'formData keys=', Array.from(formData.keys()));
    $.ajax({
        url: form.action,
        method: form.method,
        data: formData,
        processData: false,
        contentType: false,
        success: function (resp) {
            var modalId = $(form).closest('.modal').attr('id');
            $('#' + modalId + ' .modal-content').html($(resp).find('.modal-content').html());
            Swal.fire('¡Éxito!', 'Acción realizada correctamente.', 'success');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('enviarAjax error:', textStatus, errorThrown, 'response:', jqXHR.responseText);
            var message = 'Ocurrió un error. Intenta de nuevo.';
            // Si el servidor devuelve un mensaje más detallado, mostrarlo
            try {
                var json = JSON.parse(jqXHR.responseText);
                if (json.message) message = json.message;
            } catch (e) {
                // no es JSON
                if (jqXHR.responseText) message = jqXHR.responseText;
            }
            Swal.fire('Error', message, 'error');
        },
        complete: function () {
            $btn.prop('disabled', false);
            enviandoFlujo = false;
        }
    });
}

    // Unifica el manejo AJAX para formularios de flujo y fotos (NO detalles)
// Mejora: si los inputs .input-latitud/.input-longitud ya contienen valores, usarlos directamente
$(document).off('submit.formFlujoFotos').on('submit.formFlujoFotos', '.form-flujo, .form-subir-fotos', function (e) {
    var form = this;
    var $btn = $(form).find('button[type=submit],button[type=button]');
    if (enviandoFlujo) return false;
    enviandoFlujo = true;
    $btn.prop('disabled', true);

    let latInput = $(form).find('.input-latitud');
    let lngInput = $(form).find('.input-longitud');

    // SIEMPRE obtener ubicación antes de enviar
    e.preventDefault();
    // Si ya tenemos lat/lng en los inputs, no pedimos geolocalización otra vez
    if (latInput.length && lngInput.length) {
        var latVal = latInput.val();
        var lngVal = lngInput.val();
        if (latVal && lngVal) {
            // Ya están llenos: enviar directamente
            function enviarAjax(form, $btn) {
                var $form = $(form);
                var action = $form.attr('action');
                var method = ($form.find('input[name=_method]').val() || $form.attr('method') || 'POST').toUpperCase();
                var formData = new FormData(form);

                $btn = $btn || $form.find('button[type=submit]');
                var originalBtnHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    url: action,
                    type: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.html) {
                            // Replace modal body with returned html
                            $('#modalDetalle .modal-content').html(res.html);
                        } else if (res.success) {
                            if (res.message) Swal.fire('OK', res.message, 'success');

                            // If the response contains updated photo paths and row info, update the table row thumbnails
                            if (res.photos && res.row_id) {
                                var $row = $('#' + res.row_id);
                                if ($row.length) {
                                    var thumbsHtml = '';
                                    res.photos.forEach(function (p) {
                                        thumbsHtml += '<img src="' + p.url + '" style="height:50px;margin-right:6px;border-radius:4px;" />';
                                    });
                                    // Find the cell that contains thumbnails - try to match by class
                                    var $cell = $row.find('.td-fotos');
                                    if ($cell.length) {
                                        $cell.html(thumbsHtml);
                                    } else {
                                        // fallback: replace second td
                                        $row.find('td').eq(1).html(thumbsHtml);
                                    }
                                }
                            }
                        }
                    },
                    error: function (xhr, status, err) {
                        console.error('AJAX Error:', status, err);
                        console.error(xhr.responseText);
                        Swal.fire('Error', 'Ocurrió un error en la petición. Revisa la consola.', 'error');
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html(originalBtnHtml);
                    }
                });
            }
                    var filesInput = $(form).find('input[type=file]')[0];
                    if (filesInput && filesInput.files.length > 5) {
                        e.preventDefault();
                        $btn.prop('disabled', false);
                        Swal.fire({ icon: 'warning', title: 'Máximo 5 fotos', text: 'No puedes subir más de 5 fotos a la vez.' });
                        return false;
                    }
                    // si pasa, dejar que el manejador general lo procese (se enviará por AJAX)
                });

                // Handler para eliminar foto (botones dinámicos)
                $(document).off('click.eliminarFoto').on('click.eliminarFoto', '.btn-eliminar-foto', function (e) {
                    e.preventDefault();
                    var $btn = $(this);
                    var index = $btn.data('index');
                    var url = '{{ route("inspecciones.eliminarFoto", [$inspeccion->id, $f->funcionario_id]) }}';
                    Swal.fire({
                        title: '¿Eliminar foto?',
                        text: 'Esta acción no se puede deshacer.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: url,
                                method: 'POST',
                                data: { index: index },
                                dataType: 'html',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                success: function (resp) {
                                    // Reemplaza el contenido del modal con la respuesta renderizada
                                    var modalId = $btn.closest('.modal').attr('id');
                                    $('#' + modalId + ' .modal-content').html($(resp).find('.modal-content').html());
                                    Swal.fire('Eliminada', 'Foto eliminada correctamente.', 'success');
                                },
                                error: function (xhr, textStatus, errorThrown) {
                                    console.error('Eliminar foto error:', textStatus, errorThrown, xhr.responseText);
                                    Swal.fire('Error', 'No se pudo eliminar la foto. Revisa la consola.', 'error');
                                }
                            });
                        }
                    });
                });

                // Deshabilitar input de subida si ya hay 5 fotos o si salida_obra está marcada
                (function(){
                    var fotoCount = {{ !empty($fotos) ? count($fotos) : 0 }};
                    var tieneSalida = {{ is_null($f->hora_salida_obra) ? 'false' : 'true' }};
                    if (fotoCount >= 5 || tieneSalida) {
                        $('.form-subir-fotos input[type=file]').prop('disabled', true);
                        $('.form-subir-fotos button[type=submit]').prop('disabled', true);
                    }
                })();

$(function() {
    // Handler específico para el modal actual: evita depender de la variable $inspecciones
    $('#btnUbicacionActual{{ $inspeccion->id }}_{{ $f->id }}').off('click').on('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                $('#latitudActual{{ $inspeccion->id }}_{{ $f->id }}').val(position.coords.latitude);
                $('#longitudActual{{ $inspeccion->id }}_{{ $f->id }}').val(position.coords.longitude);
                $('#formUbicacionActual{{ $inspeccion->id }}_{{ $f->id }}').submit();
            }, function(error) {
                alert('No se pudo obtener la ubicación: ' + error.message);
            });
        } else {
            alert('Geolocalización no soportada por el navegador.');
        }
    });
});
</script>
@endpush