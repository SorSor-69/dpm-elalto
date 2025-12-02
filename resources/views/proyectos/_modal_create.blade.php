<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-labelledby="modalCreateLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <form id="formCreate" action="{{ route('proyectos.store') }}" method="POST">
        @csrf
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalCreateLabel">➕ REGISTRAR PROYECTO</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <!-- NOMBRE Y DISTRITO -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="nombre">📌 Nombre del Proyecto</label>
              <input type="text" class="form-control text-uppercase" id="nombre" name="nombre" required>
            </div>
            <div class="form-group col-md-6">
              <label for="distrito">🏙️ Distrito</label>
              <select class="form-control" id="distrito" name="distrito" required>
                <option value="">Seleccione un distrito</option>
                @for ($i = 1; $i <= 14; $i++)
          <option value="D-{{ $i }}">D-{{ $i }}</option>
        @endfor
              </select>
            </div>
          </div>

          <!-- PRESUPUESTO -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="presupuesto">💰 Presupuesto (Bs)</label>
              <input type="number" class="form-control" id="presupuesto" name="presupuesto" step="0.01" min="0"
                required>
            </div>
            <div class="form-group col-md-6">
              <label for="estado">📈 Estado</label>
              <input type="text" class="form-control" id="estado" name="estado" value="Nuevo" readonly>
            </div>
          </div>
          <!-- DESCRIPCIÓN -->
          <div class="form-row">
            <div class="form-group col-12">
              <label for="descripcion">📝 Descripción del Proyecto</label>
              <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                placeholder="Ingrese una breve descripción del proyecto" required></textarea>
            </div>
          </div>
          <script>
            // Forzar mayúsculas al escribir en la descripción
            document.addEventListener('DOMContentLoaded', function () {
              const descripcion = document.getElementById('descripcion');
              if (descripcion) {
                descripcion.addEventListener('input', function () {
                  this.value = this.value.toUpperCase();
                });
              }
            });
          </script>

          <!-- MAPA -->
          <div class="form-group">
            <label>🗺️ Ubicación del Proyecto</label>
            <div id="mapCreate" style="height: 500px; border-radius: 10px;"></div>
            <small class="form-text text-muted mt-2">
              Mueve el marcador o haz clic en el mapa para seleccionar la ubicación. Se calculará una ruta desde el GAM
              de El Alto.
            </small>
            <div class="mt-3">
              <input type="hidden" id="latitud" name="latitud">
              <input type="hidden" id="longitud" name="longitud">
              <p><strong>🛣️ Distancia:</strong> <span id="create_distancia">--</span></p>
              <p><strong>⏱️ Tiempo estimado de llegada:</strong> <span id="create_duracion">--</span></p>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Guardar Proyecto</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('css')
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
  <style>
    .leaflet-routing-container {
    display: none !important;
    /* Oculta la lista de pasos */
    }

    .text-uppercase {
    text-transform: uppercase;
    }
  </style>
@endpush

@push('js')
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>
  <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

  <script>
    const puntoBase = L.latLng(-16.516522, -68.221121); // GAM El Alto
    let mapCreate, markerCreate, routingCreate;

    $('#modalCreate').on('shown.bs.modal', function () {
    if (mapCreate) {
      mapCreate.remove();
      routingCreate = null;
    }

    mapCreate = L.map('mapCreate').setView(puntoBase, 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(mapCreate);

    markerCreate = L.marker(puntoBase, { draggable: true }).addTo(mapCreate);
    markerCreate.on('dragend', function () {
      const latlng = markerCreate.getLatLng();
      $('#latitud').val(latlng.lat.toFixed(6));
      $('#longitud').val(latlng.lng.toFixed(6));
      rutaCreate(latlng);
    });

    mapCreate.on('click', function (e) {
      markerCreate.setLatLng(e.latlng);
      $('#latitud').val(e.latlng.lat.toFixed(6));
      $('#longitud').val(e.latlng.lng.toFixed(6));
      rutaCreate(e.latlng);
    });

    L.Control.geocoder({
      defaultMarkGeocode: false,
      position: 'topright'
    }).on('markgeocode', function (e) {
      const latlng = e.geocode.center;
      markerCreate.setLatLng(latlng);
      mapCreate.setView(latlng, 16);
      $('#latitud').val(latlng.lat.toFixed(6));
      $('#longitud').val(latlng.lng.toFixed(6));
      rutaCreate(latlng);
    }).addTo(mapCreate);

    $('#latitud').val(puntoBase.lat);
    $('#longitud').val(puntoBase.lng);
    rutaCreate(puntoBase);
    });

    function rutaCreate(destino) {
    if (routingCreate) {
      mapCreate.removeControl(routingCreate);
    }

    routingCreate = L.Routing.control({
      waypoints: [puntoBase, L.latLng(destino.lat, destino.lng)],
      lineOptions: { styles: [{ color: 'green', weight: 4 }] },
      createMarker: () => null,
      addWaypoints: false,
      draggableWaypoints: false
    }).on('routesfound', function (e) {
      const route = e.routes[0];
      const distancia = (route.summary.totalDistance / 1000).toFixed(2);
      const duracion = Math.round(route.summary.totalTime / 60 * 3);
      $('#create_distancia').text(`${distancia} km`);
      $('#create_duracion').text(`${duracion} min`);
    }).addTo(mapCreate);
    }
  </script>
@endpush