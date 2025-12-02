@extends('adminlte::page')

@section('title', 'Historial de Inspecciones')

@section('content_header')
    <h1>HISTORIAL DE FUNCIONARIOS</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="historial-table">
                <thead>
                    <tr>
                        <th>Funcionario</th>
                        <th>Cargo</th>
                        <th>Estado</th>
                        <th>Total de inspecciones</th>
                        <th>Más detalles</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($funcionarios as $f)
                        @php
                            $total = $inspeccionesCounts[$f->id] ?? 0;
                            $porcentaje = $max > 0 ? round(($total / $max) * 100) : 0;
                        @endphp
                        <tr>
                            <td>{{ $f->nombres }} {{ $f->apellidos }}</td>
                            <td>{{ $f->cargo }}</td>
                            <td>
                                @if(isset($f->activo) ? $f->activo : $f->estado === 'activo' || $f->estado === '1')
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="mr-2">{{ $total }}</span>
                                    <div style="flex:1;">
                                        <div class="progress" style="height:16px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $porcentaje }}%;" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary btn-detalles" data-id="{{ $f->id }}">Más detalles</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal detalles -->
<div class="modal fade" id="detallesModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalles de Inspecciones</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="detallesContent">
        <!-- Cargado por AJAX -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

@stop

@section('js')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(function() {
            var table = $('#historial-table').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                }
            });

            // Detalles AJAX
            $('#historial-table').on('click', '.btn-detalles', function() {
                var id = $(this).data('id');
                var url = '{{ url("historial/detalles") }}/' + id;
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                    .then(r => r.text())
                    .then(html => {
                        document.getElementById('detallesContent').innerHTML = html;
                        $('#detallesModal').modal('show');
                    })
                    .catch(() => alert('Error cargando detalles'));
            });
        });
    </script>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
@endsection
