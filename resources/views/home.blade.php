@extends('adminlte::page')

@section('title', 'Panel de Proyectos El Alto')

@section('content_header')
    <div class="mb-3">
        <h1 class="text-primary"><i class="fas fa-tachometer-alt"></i> Bienvenido al Panel de Proyectos Municipales</h1>
    </div>
    <h4><i class="fas fa-project-diagram text-primary"></i> Proyectos de la Ciudad de El Alto</h4>
@stop

@section('content')
<!-- Tarjetas resumen pequeñas y clickeables -->
<div class="row mb-3">
    <div class="col-md-3">
        <button class="card bg-success text-white w-100 estado-btn" data-estado="NUEVO" style="cursor:pointer;">
            <div class="card-body p-2 text-center">
                <h6 class="mb-1">Nuevos</h6>
                <span style="font-size:1.5em;">{{ $proyectos->where('estado', 'NUEVO')->count() }}</span>
            </div>
        </button>
    </div>
    <div class="col-md-3">
        <button class="card bg-info text-white w-100 estado-btn" data-estado="EN PROCESO" style="cursor:pointer;">
            <div class="card-body p-2 text-center">
                <h6 class="mb-1">En Proceso</h6>
                <span style="font-size:1.5em;">{{ $proyectos->where('estado', 'EN PROCESO')->count() }}</span>
            </div>
        </button>
    </div>
    <div class="col-md-3">
        <button class="card bg-primary text-white w-100 estado-btn" data-estado="CONCLUIDO" style="cursor:pointer;">
            <div class="card-body p-2 text-center">
                <h6 class="mb-1">Concluidos</h6>
                <span style="font-size:1.5em;">{{ $proyectos->where('estado', 'CONCLUIDO')->count() }}</span>
            </div>
        </button>
    </div>
    <div class="col-md-3">
        <button class="card bg-dark text-white w-100 estado-btn" data-estado="" style="cursor:pointer;">
            <div class="card-body p-2 text-center">
                <h6 class="mb-1">Total Proyectos</h6>
                <span style="font-size:1.5em;">{{ $totalProyectos }}</span>
            </div>
        </button>
    </div>
</div>

<!-- Filtro de distritos -->
<div class="row mb-3">
    <div class="col-md-6">
        <form method="GET" action="{{ route('home') }}">
            <label for="distrito">Selecciona Distritos:</label>
            <select name="distrito[]" id="distrito" class="form-control" multiple>
                @foreach($distritos as $d)
                    <option value="{{ $d }}" {{ in_array($d, $selectedDistritos) ? 'selected' : '' }}>{{ $d }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

<!-- Mapa y gráfico vertical -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-map-marked-alt"></i> Mapa de Proyectos
            </div>
            <div class="card-body" style="height: 400px;">
                <div id="map" style="height: 100%;"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <!-- Panel de porcentajes con navbar mejorada -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                <ul class="nav nav-pills" id="tipoGraficoTabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" data-grafico="bar"><i class="fas fa-chart-bar"></i> Barras</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-grafico="pie"><i class="fas fa-chart-pie"></i> Circular</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-grafico="list"><i class="fas fa-list"></i> Listado</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div id="graficoBar" style="display:block;">
                    <canvas id="barChartDistritos" height="250"></canvas>
                </div>
                <div id="graficoPie" style="display:none; text-align:center;">
                    <canvas id="pieChartDistritos" height="400" width="400" style="max-width:500px;"></canvas>
                </div>
                <div id="graficoList" style="display:none;">
                    @foreach($distritos as $distrito)
                        @php
                            $count = $proyectos->where('distrito', $distrito)
                                ->when(request('estado'), function($q) {
                                    return $q->where('estado', request('estado'));
                                })->count();
                            $percent = $totalProyectos > 0 ? round(($count / $totalProyectos) * 100) : 0;
                        @endphp
                        @if($count > 0)
                        <div class="mb-3">
                            <span class="font-weight-bold">Distrito {{ str_replace(['D-', 'D'], '', $distrito) }}</span>
                            <span class="badge badge-info float-right">{{ $count }} proyectos</span>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted">{{ $percent }}%</small>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
.card {
    box-shadow: 0 2px 8px rgba(205, 15, 15, 0.07);
    border-radius: 10px;
}
.card-header {
    border-radius: 10px 10px 0 0 !important;
}
.progress-bar {
    transition: width 0.5s;
}
.estado-btn:hover {
    box-shadow: 0 0 10px #007bff;
    transform: scale(1.05);
    transition: all 0.2s;
}
.nav-pills .nav-link {
    color: #fff;
    background: transparent;
    margin-left: 5px;
    margin-right: 5px;
    border-radius: 20px;
    font-weight: 500;
    transition: background 0.2s;
}
.nav-pills .nav-link.active, .nav-pills .nav-link:hover {
    background: #0069d9;
    color: #fff;
}
#graficoPie {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 420px;
}
#pieChartDistritos {
    max-width: 500px;
    max-height: 500px;
}
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        $('#distrito').select2({
            placeholder: "Selecciona Distritos",
            allowClear: true,
            width: '100%'
        }).on('change', function() {
            $(this).closest('form').submit();
        });

        // Filtrado por estado desde las tarjetas
        $('.estado-btn').click(function() {
            let estado = $(this).data('estado');
            // Obtén los distritos seleccionados y arma la URL correctamente
            let distritos = $('#distrito').val() || [];
            let params = new URLSearchParams();
            params.append('estado', estado);
            distritos.forEach(d => params.append('distrito[]', d));
            window.location.href = "{{ route('home') }}?" + params.toString();
        });

        // Cambiar entre gráficos
        $('#tipoGraficoTabs a').click(function(e) {
            e.preventDefault();
            $('#tipoGraficoTabs a').removeClass('active');
            $(this).addClass('active');
            let tipo = $(this).data('grafico');
            $('#graficoBar, #graficoPie, #graficoList').hide();
            if(tipo === 'bar') $('#graficoBar').show();
            if(tipo === 'pie') $('#graficoPie').show();
            if(tipo === 'list') $('#graficoList').show();
        });
    });

    // Obtener estado filtrado desde backend (si lo envías)
    let estadoFiltrado = "{{ request('estado') }}";

    // Leaflet
    const map = L.map('map').setView([-16.516522, -68.221121], 12); // El Alto
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    @foreach($proyectos as $proyecto)
        @if($proyecto->latitud && $proyecto->longitud)
            @if(!request('estado') || request('estado') === $proyecto->estado)
                L.marker([{{ $proyecto->latitud }}, {{ $proyecto->longitud }}])
                    .addTo(map)
                    .bindPopup("<strong>{{ $proyecto->nombre }}</strong><br>{{ $proyecto->distrito }}<br>{{ $proyecto->descripcion }}");
            @endif
        @endif
    @endforeach

    // Chart.js datos
    let labels = [];
    let labelsFull = [];
    let data = [];
    @foreach($distritos as $distrito)
        @php
            $count = $proyectos->where('distrito', $distrito)
                ->when(request('estado'), function($q) {
                    return $q->where('estado', request('estado'));
                })->count();
        @endphp
        @if($count > 0)
            labels.push("{{ $distrito }}");
            labelsFull.push("Distrito {{ str_replace(['D-', 'D'], '', $distrito) }}");
            data.push({{ $count }});
        @endif
    @endforeach

    // Barras verticales
    const barChart = new Chart(document.getElementById('barChartDistritos').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labelsFull,
            datasets: [{
                label: 'Proyectos',
                data: data,
                backgroundColor: 'rgba(40, 167, 69, 0.7)'
            }]
        },
        options: {
            indexAxis: 'x',
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // Circular
    const pieChart = new Chart(document.getElementById('pieChartDistritos').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: labelsFull,
            datasets: [{
                data: data,
                backgroundColor: [
                    '#007bff', '#28a745', '#17a2b8', '#ffc107', '#dc3545', '#6c757d', '#20c997', '#6610f2', '#fd7e14', '#e83e8c', '#343a40', '#f8f9fa', '#6f42c1', '#198754'
                ]
            }]
        },
        options: {
            cutout: '50%', // Menos espacio en el centro, más circular
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
@endpush