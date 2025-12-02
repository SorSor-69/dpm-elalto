<div class="modal fade" id="modalMap{{ $proyecto->id }}" tabindex="-1" role="dialog" aria-labelledby="modalMapLabel{{ $proyecto->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalMapLabel{{ $proyecto->id }}">
                    <i class="fas fa-map-marked-alt"></i> Ubicación del Proyecto
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="mapProyecto{{ $proyecto->id }}" style="height: 400px; border-radius: 10px;"></div>
                <div class="mt-2">
                    <strong>🛣️ Distancia:</strong> <span id="distanciaProyecto{{ $proyecto->id }}">--</span><br>
                    <strong>⏱️ Tiempo estimado:</strong> <span id="duracionProyecto{{ $proyecto->id }}">--</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
(function() {
    // Coordenadas del GAMEA El Alto
    const puntoBase = L.latLng(-16.516522, -68.221121);

    let mapProyecto{{ $proyecto->id }} = null;
    let routingControl{{ $proyecto->id }} = null;

    $('#modalMap{{ $proyecto->id }}').on('shown.bs.modal', function () {
        // Elimina el mapa anterior si existe
        if (mapProyecto{{ $proyecto->id }}) {
            mapProyecto{{ $proyecto->id }}.remove();
            routingControl{{ $proyecto->id }} = null;
        }

        var lat = {{ $proyecto->latitud ?? 0 }};
        var lng = {{ $proyecto->longitud ?? 0 }};
        var mapId = 'mapProyecto{{ $proyecto->id }}';
        var mapDiv = document.getElementById(mapId);
        mapDiv.innerHTML = "";

        mapProyecto{{ $proyecto->id }} = L.map(mapId).setView([lat, lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(mapProyecto{{ $proyecto->id }});

        // Marcador del proyecto
        L.marker([lat, lng]).addTo(mapProyecto{{ $proyecto->id }})
            .bindPopup('Proyecto: {{ $proyecto->nombre }}').openPopup();

        // Ruta desde el GAM El Alto hasta el proyecto
        routingControl{{ $proyecto->id }} = L.Routing.control({
            waypoints: [
                puntoBase,
                L.latLng(lat, lng)
            ],
            lineOptions: { styles: [{ color: 'green', weight: 4 }] },
            createMarker: function(i, wp, nWps) {
                if (i === 0) {
                    return L.marker(wp.latLng, {icon: L.icon({iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png', iconSize: [32,32]})})
                        .bindPopup('GAM El Alto');
                } else {
                    return L.marker(wp.latLng);
                }
            },
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: true,
            routeWhileDragging: false
        }).on('routesfound', function(e) {
            const route = e.routes[0];
            const distancia = (route.summary.totalDistance / 1000).toFixed(2);
            // Multiplica el tiempo estimado por 3
            const duracion = Math.round(route.summary.totalTime / 60) * 3;
            $('#distanciaProyecto{{ $proyecto->id }}').text(`${distancia} km`);
            $('#duracionProyecto{{ $proyecto->id }}').text(`${duracion} min`);
        }).addTo(mapProyecto{{ $proyecto->id }});

        setTimeout(function() {
            mapProyecto{{ $proyecto->id }}.invalidateSize();
        }, 200);
    });

    // Limpia el mapa al cerrar el modal (opcional, para liberar memoria)
    $('#modalMap{{ $proyecto->id }}').on('hidden.bs.modal', function () {
        if (mapProyecto{{ $proyecto->id }}) {
            mapProyecto{{ $proyecto->id }}.remove();
            mapProyecto{{ $proyecto->id }} = null;
            routingControl{{ $proyecto->id }} = null;
        }
        $('#distanciaProyecto{{ $proyecto->id }}').text('--');
        $('#duracionProyecto{{ $proyecto->id }}').text('--');
    });
})();
</script>
@endpush