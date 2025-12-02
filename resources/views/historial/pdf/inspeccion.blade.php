<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Inspección - {{ $inspeccion->fecha ?? now()->format('Y-m-d') }}</title>
    <style>
        @page {
            margin: 2.5cm 2cm;
        }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
        }
        .logo {
            margin-bottom: 15px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }
        .meta-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .meta-info table {
            width: 100%;
        }
        .meta-info td {
            padding: 5px 10px;
            border: none;
        }
        .meta-info .label {
            font-weight: bold;
            color: #495057;
            width: 30%;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border: 1px solid #dee2e6;
        }
        th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .photos {
            margin-top: 20px;
        }
        .photos-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
        }
        .photo-container {
            text-align: center;
        }
        .photo-container img {
            width: 300px;
            height: 200px;
            object-fit: cover;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 0 auto;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
            font-size: 10px;
            color: #6c757d;
            text-align: center;
        }
        .highlight {
            background-color: #e3f2fd;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .observation-box {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin: 10px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-completed {
            background-color: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            {{-- Logo removed to avoid broken-image placeholder in PDF when GD is not available --}}
        </div>
        <div class="title">Reporte de Inspección</div>
        <div class="subtitle">Gobierno Autónomo Municipal de El Alto</div>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td class="label">Funcionario:</td>
                <td><strong>{{ $funcionario->nombres ?? '' }} {{ $funcionario->apellidos ?? '' }}</strong></td>
                <td class="label">Fecha:</td>
                <td>{{ $inspeccion->fecha ?? $inspeccion->created_at->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Cargo:</td>
                <td>{{ $funcionario->cargo ?? '—' }}</td>
                <td class="label">Proyecto:</td>
                <td>{{ $proyectoNombre }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Detalle de la Inspección</div>
        <div class="highlight">
            <strong>Actividad:</strong><br>
            {{ $inspeccion->actividad ?? $inspeccion->detalle_inspeccion ?? '—' }}
        </div>

        @php
            // Mostrar los detalles de la inspección (pueden estar en la asignación o en la inspección)
            $detalles_inspeccion = $asignacion->detalle_inspeccion ?? $inspeccion->observaciones ?? null;
        @endphp
        @if($detalles_inspeccion)
        <div class="observation-box">
            <strong>Detalles de la Inspección:</strong><br>
            @if(is_array($detalles_inspeccion))
                @foreach($detalles_inspeccion as $det)
                    @if(is_array($det) && isset($det['detalle']))
                        <div>- {{ $det['detalle'] }} <small class="text-muted">({{ $det['fecha'] ?? '' }})</small></div>
                    @else
                        <div>- {{ $det }}</div>
                    @endif
                @endforeach
            @else
                {{ $detalles_inspeccion }}
            @endif
        </div>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Control de Tiempos</div>
        <table>
            <tr>
                <th style="width: 30%;">Evento</th>
                <th style="width: 35%;">Hora</th>
                <th style="width: 35%;">Estado</th>
            </tr>
            <tr>
                <td>Salida GAMEA</td>
                <td>{{ $asignacion->hora_salida_gamea ?? '—' }}</td>
                <td>
                    @if($asignacion->hora_salida_gamea)
                        <div class="status-badge status-completed">Registrado</div>
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr>
                <td>Llegada a Obra</td>
                <td>{{ $asignacion->hora_llegada_obra ?? '—' }}</td>
                <td>
                    @if($asignacion->hora_llegada_obra)
                        <div class="status-badge status-completed">Registrado</div>
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr>
                <td>Salida de Obra</td>
                <td>{{ $asignacion->hora_salida_obra ?? '—' }}</td>
                <td>
                    @if($asignacion->hora_salida_obra)
                        <div class="status-badge status-completed">Registrado</div>
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr>
                <td>Llegada GAMEA</td>
                <td>{{ $asignacion->hora_llegada_gamea ?? '—' }}</td>
                <td>
                    @if($asignacion->hora_llegada_gamea)
                        <div class="status-badge status-completed">Registrado</div>
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Duración Total</strong></td>
                <td colspan="2"><strong>{{ $inspeccion->duracion ?? $inspeccion->tiempo_inspeccion ?? '—' }}</strong></td>
            </tr>
        </table>
    </div>
    {{-- Photos disabled temporarily to avoid GD/png processing errors in dompdf --}}
    @if(false && !empty($photos_base64))
    <div class="section">
        <div class="section-title">Registro Fotográfico</div>
        <div class="photos-grid">
            @foreach($photos_base64 as $index => $foto)
            <div class="photo-container">
                <img src="{{ $foto }}" alt="Foto {{ $index + 1 }}">
                <div style="margin-top: 5px; font-size: 11px; color: #666;">
                    Imagen {{ $index + 1 }} de la inspección
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @php
        $lat = $inspeccion->latitud ?? $inspeccion->lat ?? null;
        $lng = $inspeccion->longitud ?? $inspeccion->lng ?? null;
    @endphp
    @if($lat && $lng)
    <div class="section">
        <div class="section-title">Ubicación</div>
        <div class="highlight">
            Coordenadas: {{ $lat }}, {{ $lng }}
            <br>
            <small>Ver en Google Maps: https://www.google.com/maps/search/?api=1&query={{ $lat }},{{ $lng }}</small>
        </div>
    </div>
    @endif

    <div class="footer">
        Documento generado el {{ now()->format('d/m/Y H:i:s') }} | GAMEA - Sistema de Control de Inspecciones
        <div style="margin-top: 5px">Página {PAGE_NUM} de {PAGE_COUNT}</div>
    </div>
</body>
</html>