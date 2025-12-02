<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Historial de inspecciones - {{ $funcionario->nombres ?? '' }} {{ $funcionario->apellidos ?? '' }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size:12px; color:#222 }
        h1,h2,h3{ margin:0 0 8px 0 }
        .meta { margin-bottom:12px }
        .inspeccion { border-bottom:1px solid #ddd; padding:8px 0 }
        .photos img { max-width:150px; max-height:120px; margin-right:6px; margin-bottom:6px }
        .small { font-size:11px; color:#666 }
        table { width:100%; border-collapse:collapse }
        th,td { text-align:left; padding:6px; vertical-align:top }
    </style>
</head>
<body>
    <h1>Historial de Inspecciones</h1>
    <div class="meta">
        <strong>Funcionario:</strong> {{ $funcionario->nombres ?? '' }} {{ $funcionario->apellidos ?? '' }}<br>
        <strong>Cargo:</strong> {{ $funcionario->cargo ?? '—' }}<br>
        <strong>Generado:</strong> {{ now()->format('Y-m-d H:i') }}
    </div>

    @if($detalles->isEmpty())
        <div>No hay inspecciones registradas para este funcionario.</div>
    @else
        @foreach($detalles as $d)
            @php $ins = $d->inspeccion; @endphp
            <div class="inspeccion">
                <div style="display:flex; justify-content:space-between;">
                    <div>
                        <strong>Fecha:</strong> {{ $ins->fecha ?? $ins->created_at ?? '—' }}<br>
                        <strong>Proyecto:</strong> {{ data_get($ins, 'proyecto.nombre') ?: data_get($ins, 'proyecto_manual') ?: '—' }}
                    </div>
                    <div style="text-align:right">
                        <span class="small">Registrado: {{ $d->created_at }}</span>
                    </div>
                </div>

                <div style="margin-top:8px">
                    <strong>Actividad / Detalle:</strong> {{ $ins->actividad ?? $ins->detalle_inspeccion ?? '—' }}
                </div>
                <div style="margin-top:4px">
                    <strong>Salida GAMEA:</strong> {{ $d->hora_salida_gamea ?? $ins->hora_salida_gamea ?? '—' }} &nbsp; 
                    <strong>Llegada obra:</strong> {{ $d->hora_llegada_obra ?? $ins->hora_llegada_obra ?? '—' }} &nbsp; 
                    <strong>Salida obra:</strong> {{ $d->hora_salida_obra ?? $ins->hora_salida_obra ?? '—' }} &nbsp; 
                    <strong>Llegada GAMEA:</strong> {{ $d->hora_llegada_gamea ?? $ins->hora_llegada_gamea ?? '—' }}
                </div>
                <div style="margin-top:4px">
                    <strong>Duración:</strong> {{ $ins->duracion ?? $ins->tiempo_inspeccion ?? '—' }}
                </div>
                <div style="margin-top:4px">
                    <strong>Observaciones:</strong> {{ $ins->observaciones ?? '—' }}
                </div>

                @php
                    $photos = [];
                    if (!empty($d->fotos) && is_array($d->fotos)) {
                        $photos = $d->fotos;
                    } elseif (!empty($d->foto_llegada_obra)) {
                        $photos = json_decode($d->foto_llegada_obra, true) ?: [];
                    } elseif (!empty($d->foto_llegada_obra) && is_array($d->foto_llegada_obra)) {
                        $photos = $d->foto_llegada_obra;
                    }
                @endphp

                @if(!empty($photos))
                    <div style="margin-top:8px"><strong>Fotos:</strong></div>
                    <div class="photos">
                        @foreach($photos as $foto)
                            <img src="{{ public_path('storage/' . $foto) ? public_path('storage/' . $foto) : (asset('storage/' . $foto)) }}" alt="foto" />
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</body>
</html>
