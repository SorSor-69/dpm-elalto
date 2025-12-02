@extends('adminlte::page')

@section('title', 'Desempeño de Funcionarios')

@section('content_header')
    <h1>Desempeño de Funcionarios</h1>
@endsection

@section('content')
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center">
            
            <form method="GET" action="{{ route('desempeño.index') }}" class="moderno-selector-linea">
                <label for="periodo" class="mb-0 font-weight-bold periodo-label">Periodo:</label>
                <select name="periodo" id="periodo" class="form-control form-control-modern periodo-select" onchange="updateSelector()">
                    <option value="semana" {{ request('periodo', 'mes') == 'semana' ? 'selected' : '' }}>Semanal</option>
                    <option value="mes" {{ request('periodo', 'mes') == 'mes' ? 'selected' : '' }}>Mensual</option>
                    <option value="año" {{ request('periodo', 'mes') == 'año' ? 'selected' : '' }}>Anual</option>
                </select>
                <!-- Selector de 'Funcionario' eliminado por petición del usuario -->
                <label for="mes" class="mb-0 font-weight-bold mes-label" style="display:none;">Mes:</label>
                <select name="mes" id="mes" class="form-control form-control-mes mes-select" style="display:none;">
                    @php \Carbon\Carbon::setLocale('es'); @endphp
                    @for ($m = 1; $m <= 12; $m++)
                        @php
                            $mesNombre = \Carbon\Carbon::create(null, $m)->monthName;
                        @endphp
                        <option value="{{ $m }}" {{ request('mes', date('n')) == $m ? 'selected' : '' }}>{{ ucfirst($mesNombre) }}</option>
                    @endfor
                </select>
                <label for="año" class="mb-0 font-weight-bold año-label" style="display:none;">Año:</label>
                <select name="año" id="año" class="form-control form-control-año año-select" style="display:none;">
                    @for ($y = date('Y')-3; $y <= date('Y'); $y++)
                        <option value="{{ $y }}" {{ request('año', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <label for="semana" class="mb-0 font-weight-bold semana-label" style="display:none;">Semana:</label>
                <select name="semana" id="semana" class="form-control form-control-semana semana-select" style="display:none;">
                    @php
                        $carbon = \Carbon\Carbon::create(request('año', date('Y')), request('mes', date('n')));
                        $firstDay = $carbon->copy()->startOfMonth();
                        $lastDay = $carbon->copy()->endOfMonth();
                        $weeks = [];
                        $week = 1;
                        $day = $firstDay->copy();
                        while ($day->lte($lastDay)) {
                            $start = $day->copy()->startOfWeek();
                            $end = $day->copy()->endOfWeek();
                            if ($start->month != $carbon->month) $start = $day->copy();
                            if ($end->month != $carbon->month) $end = $lastDay->copy();
                            $weeks[$week] = [$start->format('d M'), $end->format('d M')];
                            $day = $end->addDay();
                            $week++;
                        }
                        $currentWeek = request('semana', 1);
                    @endphp
                    @foreach ($weeks as $w => $range)
                        <option value="{{ $w }}" {{ $currentWeek == $w ? 'selected' : '' }}>Semana {{ $w }} ({{ $range[0] }} - {{ $range[1] }})</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-modern" id="filtrarBtn">Filtrar</button>
            </form>
        </div>
        <div class="card-body bg-light">
            <div id="ajax-message" style="display:none; font-weight:bold; color:#38b2ac; font-size:1.1rem; margin-bottom:10px;">Cargando desempeño...</div>
            <div class="table-responsive" id="tabla-desempeño">
                <table class="table table-bordered table-hover table-striped table-sm moderno-table" style="width:100%">
                    <thead class="thead-dark sticky-top">
                        <tr>
                            <th>#</th>
                            <th>Funcionario</th>
                            <th>Cargo</th>
                            <th>Inspecciones realizadas</th>
                            <th>Desempeño (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $max = $funcionarios->max('desempeño');
                        @endphp
                        @foreach ($funcionarios as $i => $funcionario)
                            <tr class="moderno-row">
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    {{ $funcionario->nombres }} {{ $funcionario->apellidos }}
                                    @if($funcionario->desempeño == $max && $max > 0)
                                        <span class="badge badge-gradient">Top</span>
                                    @endif
                                </td>
                                <td>{{ $funcionario->cargo }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $funcionario->inspecciones_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <div class="progress moderno-progress" style="height: 26px;">
                                        <div class="progress-bar moderno-bar" role="progressbar" style="width: {{ $funcionario->desempeño ?? 0 }}%; background: linear-gradient(90deg, #38b2ac 0%, #4299e1 50%, #48bb78 100%);" aria-valuenow="{{ $funcionario->desempeño ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                                            {{ $funcionario->desempeño ?? 0 }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .moderno-table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .moderno-row:hover {
            background-color: #f0f4f8 !important;
            transition: background 0.2s;
        }
        .moderno-progress {
            background-color: #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(56,178,172,0.12);
        }
        .moderno-bar {
            font-weight: bold;
            font-size: 1.1rem;
            border-radius: 14px;
            color: #fff;
            box-shadow: 0 1px 6px rgba(66,153,225,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .badge-gradient {
            background: linear-gradient(90deg, #38b2ac 0%, #4299e1 100%);
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            padding: 3px 10px;
            margin-left: 8px;
            font-size: 0.85rem;
            box-shadow: 0 1px 4px rgba(56,178,172,0.18);
        }
        .badge-info {
            background: #4299e1;
            color: #fff;
            font-weight: 500;
            border-radius: 8px;
            padding: 3px 10px;
            font-size: 0.95rem;
        }
        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 2;
        }
        .moderno-selector-linea {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 12px;
            flex-wrap: nowrap;
            width: 100%;
        }
        .moderno-selector-linea label {
            color: #fff;
            font-weight: 600;
            margin-right: 4px;
            margin-bottom: 0;
            white-space: nowrap;
        }
        .moderno-selector-linea select {
            margin-bottom: 0;
        }
        .form-control-modern, .form-control-mes, .form-control-año, .form-control-semana {
            border-radius: 8px;
            border: 1px solid #38b2ac;
            background: #f8fafc;
            color: #2d3748;
            font-weight: 500;
            box-shadow: 0 1px 4px rgba(56,178,172,0.10);
            transition: border-color 0.2s;
            min-width: 120px;
        }
        .form-control-mes {
            border: 1px solid #48bb78;
            background: #e6fffa;
        }
        .form-control-año {
            border: 1px solid #4299e1;
            background: #edf2f7;
        }
        .form-control-semana {
            border: 1px solid #ecc94b;
            background: #fffbea;
        }
        .form-control-modern:focus, .form-control-mes:focus, .form-control-año:focus, .form-control-semana:focus {
            border-color: #4299e1;
            box-shadow: 0 2px 8px rgba(66,153,225,0.12);
        }
        .btn-modern {
            border-radius: 8px;
            font-weight: 600;
            background: linear-gradient(90deg, #38b2ac 0%, #4299e1 100%);
            border: none;
            color: #fff;
            box-shadow: 0 1px 6px rgba(66,153,225,0.15);
        }
        .btn-modern:hover {
            background: linear-gradient(90deg, #4299e1 0%, #38b2ac 100%);
            color: #fff;
        }
    </style>
    <!-- DataTables JS (se agregan aquí para facilitar re-init desde este archivo) -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script>
        function updateSelector() {
            var periodo = document.getElementById('periodo').value;
            var mes = document.getElementById('mes');
            var año = document.getElementById('año');
            var semana = document.getElementById('semana');
            var mesLabel = document.querySelector('.mes-label');
            var añoLabel = document.querySelector('.año-label');
            var semanaLabel = document.querySelector('.semana-label');
            // Mostrar/ocultar según periodo
            if (periodo === 'mes') {
                mes.style.display = 'inline-block';
                mesLabel.style.display = 'inline-block';
                año.style.display = 'inline-block';
                añoLabel.style.display = 'inline-block';
                semana.style.display = 'none';
                semanaLabel.style.display = 'none';
            } else if (periodo === 'año') {
                mes.style.display = 'none';
                mesLabel.style.display = 'none';
                año.style.display = 'inline-block';
                añoLabel.style.display = 'inline-block';
                semana.style.display = 'none';
                semanaLabel.style.display = 'none';
            } else if (periodo === 'semana') {
                mes.style.display = 'inline-block';
                mesLabel.style.display = 'inline-block';
                año.style.display = 'inline-block';
                añoLabel.style.display = 'inline-block';
                semana.style.display = 'inline-block';
                semanaLabel.style.display = 'inline-block';
            }
        }
        document.addEventListener('DOMContentLoaded', updateSelector);

        // Función para inicializar DataTables y re-inicializar después de AJAX
        var desempeñoTable = null;
        function initDataTable() {
            try {
                if (desempeñoTable) { desempeñoTable.destroy(); }
            } catch(e) {}
            var tablaEl = document.querySelector('#tabla-desempeño table');
            if (!tablaEl) return;
            
            // Detectar el valor numérico del desempeño para ordenar correctamente
            desempeñoTable = $(tablaEl).DataTable({
                responsive: true,
                paging: true,
                searching: true,
                info: true,
                lengthChange: true,
                pageLength: 10,
                order: [[4, 'desc']], // Ordenar por columna 4 (Desempeño %) descendente
                columnDefs: [
                    { 
                        targets: 4, // Columna Desempeño (%)
                        render: function(data, type, row) {
                            if (type === 'sort' || type === 'filter') {
                                // Extraer el número del texto "XX%"
                                return parseInt(data.replace('%', '')) || 0;
                            }
                            return data;
                        }
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });
        }

        // AJAX para filtrar desempeño
        document.addEventListener('DOMContentLoaded', function() {
            initDataTable();
            const form = document.querySelector('.moderno-selector-linea');
            const tabla = document.getElementById('tabla-desempeño');
            const mensaje = document.getElementById('ajax-message');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                mensaje.style.display = 'block';
                const formData = new FormData(form);
                fetch(form.action + '?' + new URLSearchParams(formData), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.text())
                .then(html => {
                    // Extraer solo la tabla desde el HTML
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const nuevaTabla = doc.getElementById('tabla-desempeño');
                    if (nuevaTabla) {
                        tabla.innerHTML = nuevaTabla.innerHTML;
                        initDataTable();
                    }
                    mensaje.style.display = 'none';
                })
                .catch(() => {
                    mensaje.textContent = 'Error al cargar desempeño.';
                    mensaje.style.color = '#e53e3e';
                });
            });
        });
    </script>
@endsection
