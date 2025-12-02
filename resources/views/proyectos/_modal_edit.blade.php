<style>
    #modalEdit .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    /* Forzar mayúsculas en inputs de texto */
    input[type="text"] {
        text-transform: uppercase;
    }
</style>

<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content border-primary">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Editar Proyecto</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <!-- ✅ Cambiado a POST y quitado @method('PUT') -->
            <form id="formEditProyecto" method="POST" action="{{ route('proyectos.update') }}">
                @csrf
                <input type="hidden" name="id" id="edit_id">

                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit_nombre">💼 Nombre del Proyecto</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required autocomplete="off">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="edit_distrito">📍 Distrito</label>
                            <input type="text" class="form-control" id="edit_distrito" name="distrito" required autocomplete="off">
                        </div>

                        <!-- Presupuesto Actual y Aumentar presupuesto juntos lado a lado -->
                        <div class="form-group col-md-3">
                            <label>💰 Presupuesto Actual (Bs)</label>
                            <input type="number" step="0.01" class="form-control" id="edit_presupuesto_actual" disabled>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit_presupuesto">➕ Aumentar Presupuesto (Bs)</label>
                            <input type="number" step="0.01" class="form-control" id="edit_presupuesto" name="presupuesto" autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>🗺️ Ubicación del Proyecto</label>
                        <div id="mapEditContainer" style="height: 300px; border-radius: 10px; overflow: hidden;"></div>
                    </div>

                    <!-- Distancia y duración debajo del mapa -->
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>📏 Distancia desde G.A.M.E.A El Alto:</label>
                            <p class="form-control-plaintext text-success font-weight-bold" id="edit_distancia">-- km</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label>🕒 Duración estimada:</label>
                            <p class="form-control-plaintext text-primary font-weight-bold" id="edit_duracion">-- min</p>
                        </div>
                    </div>

                    <input type="hidden" id="edit_latitud" name="latitud">
                    <input type="hidden" id="edit_longitud" name="longitud">
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <i class="fas fa-times-circle"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Forzar mayúsculas mientras escriben en inputs de texto
    document.querySelectorAll('#modalEdit input[type="text"]').forEach(input => {
        input.addEventListener('input', () => {
            input.value = input.value.toUpperCase();
        });
    });
</script>
