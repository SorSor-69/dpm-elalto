<div class="modal fade" id="modalUbicacion{{ $inspeccion->id }}_{{ $f->id }}" tabindex="-1" role="dialog"
    aria-labelledby="modalUbicacionLabel{{ $inspeccion->id }}_{{ $f->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalUbicacionLabel{{ $inspeccion->id }}_{{ $f->id }}">
                    <i class="fas fa-map-marked-alt"></i> Ubicación y Ruta
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if(auth()->user()->funcionario && (string)auth()->user()->funcionario->_id === (string)$f->funcionario_id)
                    <button id="btnSubirUbicacion{{ $inspeccion->id }}_{{ $f->id }}" class="btn btn-primary btn-sm mb-2">
                        <i class="fas fa-location-arrow"></i> Subir/Actualizar Ubicación
                    </button>
                @endif
                <div id="mapaUbicacion{{ $inspeccion->id }}_{{ $f->id }}" style="height: 400px; border-radius: 10px;">
                </div>
                <div class="mt-2">
                    <strong>⏱️ Tiempo estimado de llegada del Funcionario a la Obra:</strong> <span
                        id="duracionInspeccion{{ $inspeccion->id }}_{{ $f->id }}">--</span>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    // Prioridad para la imagen del marcador:
    // 1) foto_perfil del funcionario (almacenada en storage)
    // 2) si el usuario autenticado coincide con el funcionario y tiene adminlte_image(), usarla
    // 3) fallback a UI Avatars con iniciales
    if ($f->funcionario && !empty($f->funcionario->foto_perfil)) {
        $fotoPerfil = asset('storage/' . $f->funcionario->foto_perfil);
    } elseif (auth()->check() && auth()->user()->funcionario && (string)auth()->user()->funcionario->_id === (string)$f->funcionario_id) {
        // usar la imagen que muestra en el header (adminlte_image) si existe
        $adminImg = auth()->user()->adminlte_image();
        $fotoPerfil = $adminImg ? $adminImg : 'https://ui-avatars.com/api/?name=' . urlencode((($f->funcionario->nombres ?? '') . ' ' . ($f->funcionario->apellidos ?? '')));
    } elseif ($f->funcionario) {
        $fotoPerfil = 'https://ui-avatars.com/api/?name=' . urlencode($f->funcionario->nombres . ' ' . $f->funcionario->apellidos);
    } else {
        $fotoPerfil = 'https://ui-avatars.com/api/?name=Sin+Nombre';
    }

    $latUlt = $f->latitud_actual ?? $f->latitud_llegada_gamea ?? $f->latitud_salida_obra ?? $f->latitud_foto_llegada_obra ?? $f->latitud_llegada_obra ?? $f->latitud_salida_gamea;
    $lngUlt = $f->longitud_actual ?? $f->longitud_llegada_gamea ?? $f->longitud_salida_obra ?? $f->longitud_foto_llegada_obra ?? $f->longitud_llegada_obra ?? $f->longitud_salida_gamea;
    $desc = '';
    if ($f->latitud_llegada_gamea && $f->longitud_llegada_gamea)
        $desc = 'Llegada al GAMEA';
    elseif ($f->latitud_salida_obra && $f->longitud_salida_obra)
        $desc = 'Salida de la Obra';
    elseif ($f->latitud_foto_llegada_obra && $f->longitud_foto_llegada_obra)
        $desc = 'Foto subida';
    elseif ($f->latitud_llegada_obra && $f->longitud_llegada_obra)
        $desc = 'Llegada a la Obra';
    elseif ($f->latitud_salida_gamea && $f->longitud_salida_gamea)
        $desc = 'Salida GAMEA';
    $esMismo = (auth()->user()->funcionario && (string)auth()->user()->funcionario->_id === (string)$f->funcionario_id);
@endphp

@push('js')
<script>
(function () {
    // Iconos personalizados con emoji
    const iconSalida = L.divIcon({
        className: 'custom-div-icon',
        html: '<div style="font-size:2rem;">🚩</div>',
        iconSize: [32, 32],
        iconAnchor: [16, 32]
    });
    const iconLlegada = L.divIcon({
        className: 'custom-div-icon',
        html: '<div style="font-size:2rem;">🏁</div>',
        iconSize: [32, 32],
        iconAnchor: [16, 32]
    });
    const iconFoto = L.divIcon({
        className: 'custom-div-icon',
        html: '<div style="font-size:2rem;">📸</div>',
        iconSize: [32, 32],
        iconAnchor: [16, 32]
    });
    const iconSalidaObra = L.divIcon({
        className: 'custom-div-icon',
        html: '<div style="font-size:2rem;">🚶‍♂️</div>',
        iconSize: [32, 32],
        iconAnchor: [16, 32]
    });
    const iconLlegadaGamea = L.divIcon({
        className: 'custom-div-icon',
        html: '<div style="font-size:2rem;">🏢</div>',
        iconSize: [32, 32],
        iconAnchor: [16, 32]
    });
    // Icono de perfil del funcionario
    const iconPerfil = L.icon({
        iconUrl: @json($fotoPerfil),
        iconSize: [48, 48],
        iconAnchor: [24, 48],
        className: 'rounded-circle border border-primary'
    });

    const puntoBase = L.latLng(-16.516522, -68.221121);

    let map = null, routingBase = null, routingFuncionario = null, marcadorUsuario = null;
    let latUser = null, lngUser = null;
    let watchId = null;

    function esAntesDe1630() {
        const ahora = new Date();
        return ahora.getHours() < 16 || (ahora.getHours() === 16 && ahora.getMinutes() < 30);
    }

    $('#modalUbicacion{{ $inspeccion->id }}_{{ $f->id }}').on('shown.bs.modal', function () {
        // Al abrir el modal, solicitar al servidor los datos más recientes de la asignación
        $.ajax({
            url: '{{ route("inspecciones.datosAsignacion", ["inspeccion" => $inspeccion->id, "funcionario" => $f->funcionario_id]) }}',
            method: 'GET',
            dataType: 'json'
        }).done(function(data) {
            if (data && data.found) {
                // Priorizar latitud_actual/longitud_actual si están presentes
                if (data.latitud_actual && data.longitud_actual) {
                    latUlt = data.latitud_actual;
                    lngUlt = data.longitud_actual;
                } else if (data.latitud_llegada_obra && data.longitud_llegada_obra) {
                    latUlt = data.latitud_llegada_obra;
                    lngUlt = data.longitud_llegada_obra;
                } else if (data.latitud_foto_llegada_obra && data.longitud_foto_llegada_obra) {
                    latUlt = data.latitud_foto_llegada_obra;
                    lngUlt = data.longitud_foto_llegada_obra;
                }
                // actualizar las variables locales para que el resto del código las use
                // y redibujar la ruta/marker si ya se creó el mapa
                if (map && latUlt && lngUlt) {
                    mostrarPerfil(latUlt, lngUlt);
                    if (esMismo) {
                        map.setView([latUlt, lngUlt], 17);
                        dibujarRutaFuncionario(latUlt, lngUlt);
                    } else {
                        map.setView([latUlt, lngUlt], 13);
                        dibujarRutaFuncionario(latUlt, lngUlt);
                    }
                }
            }
        }).fail(function() {
            // no bloquear si falla la petición
            console.warn('No se pudieron obtener datos recientes de la asignación.');
        });

        if (map) {
            map.remove();
            routingBase = null;
            routingFuncionario = null;
            marcadorUsuario = null;
        }

        var lat = {{ $inspeccion->proyecto->latitud ?? 0 }};
        var lng = {{ $inspeccion->proyecto->longitud ?? 0 }};
        var mapId = 'mapaUbicacion{{ $inspeccion->id }}_{{ $f->id }}';
        var mapDiv = document.getElementById(mapId);
        mapDiv.innerHTML = "";

        map = L.map(mapId).setView([lat, lng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Marcador del proyecto
        L.marker([lat, lng], { icon: iconLlegadaGamea })
            .addTo(map)
            .bindPopup('Proyecto: {{ $inspeccion->proyecto->nombre ?? "N/A" }}').openPopup();

        // Ruta verde: desde el GAM El Alto hasta el proyecto
        routingBase = L.Routing.control({
            waypoints: [
                puntoBase,
                L.latLng(lat, lng)
            ],
            lineOptions: { styles: [{ color: 'green', weight: 4 }] },
            createMarker: function (i, wp, nWps) {
                if (i === 0) {
                    return L.marker(wp.latLng, { icon: iconSalida })
                        .bindPopup('GAMEA El Alto');
                } else {
                    return L.marker(wp.latLng);
                }
            },
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: false,
            routeWhileDragging: false,
            show: false
        }).addTo(map);

        // MARCADORES DE CADA ACCIÓN (si existen)
        @if($f->hora_salida_gamea && $f->latitud_salida_gamea && $f->longitud_salida_gamea)
            L.marker([{{ $f->latitud_salida_gamea }}, {{ $f->longitud_salida_gamea }}], { icon: iconSalida })
                .addTo(map).bindPopup('🚩 Salida GAMEA<br>{{ $f->hora_salida_gamea }}');
        @endif
        @if($f->hora_llegada_obra && $f->latitud_llegada_obra && $f->longitud_llegada_obra)
            L.marker([{{ $f->latitud_llegada_obra }}, {{ $f->longitud_llegada_obra }}], { icon: iconLlegada })
                .addTo(map).bindPopup('🏁 Llegada a la Obra<br>{{ $f->hora_llegada_obra }}');
        @endif
        @if($f->foto_llegada_obra && $f->latitud_foto_llegada_obra && $f->longitud_foto_llegada_obra)
            L.marker([{{ $f->latitud_foto_llegada_obra }}, {{ $f->longitud_foto_llegada_obra }}], { icon: iconFoto })
                .addTo(map).bindPopup('📸 Foto subida');
        @endif
        @if($f->hora_salida_obra && $f->latitud_salida_obra && $f->longitud_salida_obra)
            L.marker([{{ $f->latitud_salida_obra }}, {{ $f->longitud_salida_obra }}], { icon: iconSalidaObra })
                .addTo(map).bindPopup('🚶‍♂️ Salida de la Obra<br>{{ $f->hora_salida_obra }}');
        @endif
        @if($f->hora_llegada_gamea && $f->latitud_llegada_gamea && $f->longitud_llegada_gamea)
            L.marker([{{ $f->latitud_llegada_gamea }}, {{ $f->longitud_llegada_gamea }}], { icon: iconLlegadaGamea })
                .addTo(map).bindPopup('🏢 Llegada al GAMEA<br>{{ $f->hora_llegada_gamea }}');
        @endif

        // BLOQUE DE ROLES Y ÚLTIMA UBICACIÓN
        let latUlt = @json($latUlt);
        let lngUlt = @json($lngUlt);
        let desc = @json($desc);
        let esMismo = @json($esMismo);

        function dibujarRutaFuncionario(origenLat, origenLng) {
            if (routingFuncionario) {
                map.removeControl(routingFuncionario);
            }
            routingFuncionario = L.Routing.control({
                waypoints: [
                    L.latLng(origenLat, origenLng),
                    L.latLng(lat, lng)
                ],
                lineOptions: { styles: [{ color: 'blue', weight: 4, dashArray: '10,10' }] },
                createMarker: function (i, wp, nWps) {
                    if (i === 0) {
                        return null;
                    } else {
                        return L.marker(wp.latLng);
                    }
                },
                addWaypoints: false,
                draggableWaypoints: false,
                fitSelectedRoutes: true,
                routeWhileDragging: false,
                show: false
            }).on('routesfound', function (e) {
                var routes = e.routes;
                var summary = routes[0].summary;
                var minutos = Math.round((summary.totalTime / 60) * 3);
                $('#duracionInspeccion{{ $inspeccion->id }}_{{ $f->id }}').text(minutos + ' min');
            }).addTo(map);
        }

        function mostrarPerfil(lat, lng) {
            L.marker([lat, lng], { icon: iconPerfil })
                .addTo(map)
                .bindPopup('Funcionario: {{ $f->funcionario ? ($f->funcionario->nombres . " " . $f->funcionario->apellidos) : "Sin nombre" }}');
        }

        // Mostrar la última ubicación reportada (latitud_actual) para el propio funcionario y para administradores/jefes
        if (latUlt && lngUlt) {
            mostrarPerfil(latUlt, lngUlt);
            if (esMismo) {
                // Si es el propio funcionario, hacer zoom más cercano y trazar ruta desde su ubicación
                map.setView([latUlt, lngUlt], 17);
                dibujarRutaFuncionario(latUlt, lngUlt);
            } else {
                // Si es otro usuario (ej. ADMIN o JEFE), mostrar la ubicación reportada y trazar ruta desde ahí
                map.setView([latUlt, lngUlt], 13);
                dibujarRutaFuncionario(latUlt, lngUlt);
            }
        } else if (esMismo) {
            // Solo mostrar el mensaje para el propio usuario cuando no hay ubicación registrada
            L.popup().setLatLng([lat, lng]).setContent('No hay ubicación registrada para ti.').openOn(map);
        }

        // Subir automáticamente la ubicación cuando el propio funcionario abre el modal
        if (esMismo && navigator.geolocation) {
            try {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var latAuto = position.coords.latitude;
                    var lngAuto = position.coords.longitude;
                    latUser = latAuto; lngUser = lngAuto;

                    // Enviar por AJAX la ubicación al servidor (usa la misma ruta que el botón manual)
                    $.ajax({
                        url: '{{ route("inspecciones.subirUbicacion", [$inspeccion->id, $f->funcionario_id]) }}',
                        method: 'POST',
                        data: {
                            latitud: latAuto,
                            longitud: lngAuto,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (resp) {
                            // Actualizar inputs del formulario de ubicación del modalDetalle (y de cualquier formulario con input-latitud/input-longitud)
                            var detalleModal = $("#modalDetalle{{ $inspeccion->id }}_{{ $f->id }}");
                            if (detalleModal.length) {
                                detalleModal.find('input.input-latitud').val(latAuto);
                                detalleModal.find('input.input-longitud').val(lngAuto);
                                // Quitar advertencia de tener que registrar ubicación (solo el mensaje informativo)
                                detalleModal.find('small.text-danger.d-block').filter(function() {
                                    return $(this).text().toLowerCase().includes('debes registrar tu ubicación');
                                }).remove();
                                // Habilitar el botón Salida GAMEA si existe
                                var btnSalida = detalleModal.find('#btnSalidaGamea{{ $inspeccion->id }}_{{ $f->id }}');
                                if (btnSalida.length) {
                                    btnSalida.prop('disabled', false);
                                    btnSalida.removeAttr('title');
                                }
                            }

                            // También actualizar los inputs ocultos globales si existen
                            var latInput = $("#latitudActual{{ $inspeccion->id }}_{{ $f->id }}");
                            var lngInput = $("#longitudActual{{ $inspeccion->id }}_{{ $f->id }}");
                            if (latInput.length && lngInput.length) {
                                latInput.val(latAuto);
                                lngInput.val(lngAuto);
                            }

                            // Notificación breve
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'success', title: 'Ubicación', text: 'Ubicación Registrada Correctamente.', timer: 1200, showConfirmButton: false });
                            }
                        },
                        error: function () {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo subir la ubicación automáticamente.' });
                            } else {
                                console.warn('No se pudo subir la ubicación automáticamente.');
                            }
                        }
                    });
                }, function (error) {
                    // Si el usuario niega o hay error, no hacemos nada (el usuario puede pulsar el botón manual)
                    // console.warn('Geolocation error:', error);
                }, { enableHighAccuracy: true, timeout: 10000 });
            } catch (e) {
                console.warn('Geolocation not available', e);
            }
        }

        // Si es el propio funcionario, usar geolocalización real y precisa en tiempo real SOLO si no marcó salida GAMEA y antes de 16:30
        if (esMismo && !@json($f->hora_salida_gamea) && esAntesDe1630()) {
            if (navigator.geolocation) {
                watchId = navigator.geolocation.watchPosition(function (position) {
                    latUser = position.coords.latitude;
                    lngUser = position.coords.longitude;
                    var precision = position.coords.accuracy;

                    if (marcadorUsuario) map.removeLayer(marcadorUsuario);
                    marcadorUsuario = L.marker([latUser, lngUser], { icon: iconPerfil })
                        .addTo(map)
                        .bindPopup('¡Esta es tu ubicación actual!<br>Precisión: ' + Math.round(precision) + ' m').openPopup();

                    map.setView([latUser, lngUser], 17);

                    dibujarRutaFuncionario(latUser, lngUser);

                    // SUBIR AUTOMÁTICAMENTE LA UBICACIÓN EN TIEMPO REAL (latitud_actual, longitud_actual)
                    if (precision <= 20) {
                        $.ajax({
                            url: '{{ route("funcionario.ubicacion.actual") }}',
                            method: 'POST',
                            data: {
                                latitud: latUser,
                                longitud: lngUser,
                                _token: '{{ csrf_token() }}'
                            }
                        });
                    }
                }, function (error) {
                    alert('No se pudo obtener tu ubicación precisa. Por favor, activa la ubicación en tu dispositivo.');
                }, {
                    enableHighAccuracy: true,
                    timeout: 30000,
                    maximumAge: 0
                });
            } else {
                alert('Tu navegador no soporta geolocalización.');
            }
        }
    });

    $('#modalUbicacion{{ $inspeccion->id }}_{{ $f->id }}').on('hidden.bs.modal', function () {
        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }
        if (map) {
            map.remove();
            map = null;
            routingBase = null;
            routingFuncionario = null;
            marcadorUsuario = null;
        }
    });

    // Subir ubicación por AJAX (usa la ubicación real)
    @if(auth()->user()->funcionario && auth()->user()->funcionario->id == $f->funcionario_id)
        $('#btnSubirUbicacion{{ $inspeccion->id }}_{{ $f->id }}').on('click', function () {
            if (latUser && lngUser) {
                $.ajax({
                    url: '{{ route("inspecciones.subirUbicacion", [$inspeccion->id, $f->funcionario_id]) }}',
                    method: 'POST',
                    data: {
                        latitud: latUser,
                        longitud: lngUser,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (resp) {
                        alert('Ubicación subida correctamente');
                        $('#modalUbicacion{{ $inspeccion->id }}_{{ $f->id }}').modal('hide');
                        setTimeout(function () {
                            latUser = null;
                            lngUser = null;
                            routingFuncionario = null;
                            $('#modalUbicacion{{ $inspeccion->id }}_{{ $f->funcionario_id }}').modal('show');
                        }, 1000);
                    },
                    error: function () {
                        alert('Error al subir la ubicación');
                    }
                });
            } else {
                alert('Ubicación no disponible. Espera a que el mapa cargue tu ubicación precisa.');
            }
        });
    @endif

    // Deshabilitar el botón de salida GAMEA si no hay ubicación actual registrada
    @if(auth()->user()->funcionario && auth()->user()->funcionario->id == $f->funcionario_id)
        $(document).ready(function () {
            var tieneUbicacion = {{ ($f->latitud_actual && $f->longitud_actual) ? 'true' : 'false' }};
            var btnSalidaGamea = $("#btnSalidaGamea{{ $inspeccion->id }}_{{ $f->id }}");
            if (btnSalidaGamea.length) {
                btnSalidaGamea.prop('disabled', !tieneUbicacion);
                if (!tieneUbicacion) {
                    btnSalidaGamea.attr('title', 'Primero debes registrar tu ubicación actual');
                }
            }
        });
    @endif
})();
</script>
@endpush