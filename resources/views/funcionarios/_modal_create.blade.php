<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="{{ route('funcionarios.store') }}" id="formCreateFuncionario">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">REGISTRAR NUEVO FUNCIONARIO</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    @include('funcionarios._form_fields', ['funcionario' => null])
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Guardar</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('css')
<style>
    /* Botón rojo para todos los botones que digan Cancelar */
    button, input[type="button"], input[type="submit"] {
        text-transform: none;
    }
    button.btn, input.btn[type="button"], input.btn[type="submit"] {
        font-weight: bold;
    }
    button.btn, input.btn[type="button"], input.btn[type="submit"] {
        /* No cambia color por defecto */
    }
    button.btn-danger, input.btn-danger[type="button"], input.btn-danger[type="submit"],
    button.btn[data-dismiss][data-original-title="Cancelar"],
    button.btn[data-dismiss][title="Cancelar"] {
        background: #dc3545 !important;
        color: #fff !important;
        border: none !important;
    }
    button.btn-danger:hover, button.btn-danger:focus,
    input.btn-danger[type="button"]:hover, input.btn-danger[type="button"]:focus,
    input.btn-danger[type="submit"]:hover, input.btn-danger[type="submit"]:focus {
        background: #b52a37 !important;
        color: #fff !important;
    }
    /* Botón verde para todos los botones que digan Guardar */
    button.btn-success, input.btn-success[type="button"], input.btn-success[type="submit"] {
        background: #28a745 !important;
        color: #fff !important;
        border: none !important;
    }
    button.btn-success:hover, button.btn-success:focus,
    input.btn-success[type="button"]:hover, input.btn-success[type="button"]:focus,
    input.btn-success[type="submit"]:hover, input.btn-success[type="submit"]:focus {
        background: #1e7e34 !important;
        color: #fff !important;
    }
    /* Si algún botón solo tiene el texto "Cancelar", también será rojo */
    button, input[type="button"], input[type="submit"] {
        /* Solo aplica si el texto es exactamente "Cancelar" */
    }
</style>
@endpush
