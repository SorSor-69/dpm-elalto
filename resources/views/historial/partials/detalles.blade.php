@if($detalles->isEmpty())
    <div class="alert alert-info">No hay registros para este funcionario.</div>
@else
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <strong>Historial de inspecciones</strong><br>
            <small class="text-muted">Funcionario: {{ $funcionario->nombres ?? '' }} {{ $funcionario->apellidos ?? '' }}</small>
        </div>
        <div>
            <a href="{{ url('historial/reporte/' . ($funcionario->id ?? '')) }}" target="_blank" class="btn btn-sm btn-danger">Generar PDF</a>
        </div>
    </div>

    <div class="list-group">
        @foreach($detalles as $d)
            @php
                $ins = $d->inspeccion;
                // Nombre del proyecto (relación o proyecto_manual) - use data_get for arrays/objects
                $proyectoNombre = data_get($ins, 'proyecto.nombre') ?: data_get($ins, 'proyecto_manual') ?: '—';

                // Intentar resolver el "asignador" por varios campos comunes (data_get is safe)
                $asignadorNombre = '—';
                try {
                    $asignador = data_get($ins, 'asignado_por') ?: data_get($ins, 'created_by') ?: data_get($ins, 'user_id');
                    if ($asignador) {
                        // Si es un array/obj con nombre
                        if (is_array($asignador) || is_object($asignador)) {
                            $ap = (array) $asignador;
                            $asignadorNombre = trim((($ap['nombres'] ?? $ap['nombre'] ?? '') . ' ' . ($ap['apellidos'] ?? ''))) ?: ($ap['name'] ?? '—');
                        } else {
                            // asumimos id, intentar encontrar funcionario o usuario
                            $f = \App\Models\Funcionario::find((string) $asignador);
                            if ($f) {
                                $asignadorNombre = trim((($f->nombres ?? $f->nombre ?? '') . ' ' . ($f->apellidos ?? '')));
                            } else {
                                $u = \App\Models\User::find($asignador);
                                if ($u) $asignadorNombre = $u->name;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $asignadorNombre = '—';
                }
            @endphp

            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong>Inspección</strong>
                        <div class="text-muted">Fecha: {{ $ins->fecha ?? $ins->created_at ?? 'N/A' }}</div>
                        <div><strong>Actividad:</strong> {{ $ins->actividad ?? $ins->detalle_inspeccion ?? '—' }}</div>
                    </div>
                    <div>
                        <small class="text-muted">Registrado: {{ $d->created_at }}</small>
                        <div class="mt-2 text-right">
                            <button class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#detalleInspeccionModal{{ $d->id ?? $d->_id }}">Más detalles</button>
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <div><strong>Proyecto:</strong> {{ $proyectoNombre }}</div>
                    <div><strong>Asignado por:</strong> {{ $asignadorNombre }}</div>
                    @php
                        // Consolidate photos from different possible fields
                        $photos = [];
                        if (!empty($d->fotos) && is_array($d->fotos)) {
                            $photos = $d->fotos;
                        } elseif (!empty($d->foto_llegada_obra)) {
                            $photos = json_decode($d->foto_llegada_obra, true) ?: [];
                        } elseif (!empty($d->foto_llegada_obra) && is_array($d->foto_llegada_obra)) {
                            $photos = $d->foto_llegada_obra;
                        }
                    @endphp
                </div>
            </div>

            {{-- Modal con más detalles de tiempos y fotos --}}
            <div class="modal fade" id="detalleInspeccionModal{{ $d->id ?? $d->_id }}" tabindex="-1" role="dialog" aria-labelledby="detalleInspeccionLabel{{ $d->id ?? $d->_id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="detalleInspeccionLabel{{ $d->id ?? $d->_id }}">Más detalles - Inspección</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-2"><strong>Salida GAMEA:</strong> {{ $d->hora_salida_gamea ?? $ins->hora_salida_gamea ?? '—' }}</div>
                            <div class="mb-2"><strong>Llegada a la Obra:</strong> {{ $d->hora_llegada_obra ?? $ins->hora_llegada_obra ?? '—' }}</div>
                            <div class="mb-2"><strong>Salida de la Obra:</strong> {{ $d->hora_salida_obra ?? $ins->hora_salida_obra ?? '—' }}</div>
                            <div class="mb-2"><strong>Llegada GAMEA:</strong> {{ $d->hora_llegada_gamea ?? $ins->hora_llegada_gamea ?? '—' }}</div>
                            <div class="mb-2"><strong>Duración:</strong> {{ $ins->duracion ?? $ins->tiempo_inspeccion ?? '—' }}</div>
                            <div class="mb-2"><strong>Actividad / Detalle:</strong> {{ $ins->actividad ?? $ins->detalle_inspeccion ?? '—' }}</div>
                            <div class="mb-2"><strong>Observaciones:</strong> {{ $ins->observaciones ?? '—' }}</div>
                            @php
                                $lat = $ins->latitud ?? $ins->lat ?? null;
                                $lng = $ins->longitud ?? $ins->lng ?? null;
                            @endphp
                            @if($lat && $lng)
                                <div class="mb-2"><strong>Ubicación:</strong> <a href="https://www.google.com/maps/search/?api=1&query={{ $lat }},{{ $lng }}" target="_blank">Ver en mapa</a> ({{ $lat }}, {{ $lng }})</div>
                            @endif
                            @if(!empty($photos))
                                <hr>
                                <div><strong>Fotos de la inspección</strong></div>
                                <div class="d-flex flex-wrap mt-2">
                                    @foreach($photos as $foto)
                                        <a href="{{ asset('storage/' . $foto) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $foto) }}" style="width:160px; height:100px; object-fit:cover; margin-right:8px; margin-bottom:8px;" />
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted">No hay fotos registradas.</div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <a href="{{ url('historial/inspeccion-pdf/' . ($d->id ?? $d->_id)) }}" class="btn btn-danger" target="_blank">
                                <i class="fas fa-file-pdf mr-1"></i> Generar PDF
                            </a>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
