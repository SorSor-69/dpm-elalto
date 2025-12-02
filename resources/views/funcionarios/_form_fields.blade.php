@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="row">
    <div class="form-group col-md-6">
        <label for="nombres">🧑‍💼 Nombre(s)</label>
        <input type="text" name="nombres" class="form-control" 
               value="{{ old('nombres', $funcionario->nombres ?? '') }}" 
               oninput="capitalizar(this)" required>
    </div>

    <div class="form-group col-md-6">
        <label for="apellidos">🧑‍💼 Apellido(s)</label>
        <input type="text" name="apellidos" class="form-control" 
               value="{{ old('apellidos', $funcionario->apellidos ?? '') }}" 
               oninput="capitalizar(this)" required>
    </div>
</div>

<div class="row">
    <div class="form-group col-md-6">
        <label for="ci">🪪 Cedula de Identidad</label>
        <input type="text" name="ci" class="form-control" 
               value="{{ old('ci', $funcionario->ci ?? '') }}" 
               oninput="soloNumeros(this)" required>
    </div>

    <div class="form-group col-md-3">
        <label for="complemento">🔢 Complemento (opcional)</label>
        <input type="text" name="complemento" class="form-control"
               value="{{ old('complemento', $funcionario->complemento ?? '') }}"
               maxlength="5" placeholder="Ej: A, B"
               oninput="soloLetrasMayusculas(this)">
    </div>

    <div class="form-group col-md-3">
        <label for="expedido">🌎 Expedido</label>
        <select name="expedido" class="form-control" required>
            <option value="">Seleccione</option>
            @foreach(['LP','CB','SC','OR','PT','CH','TJ','BE','PD'] as $dep)
                <option value="{{ $dep }}" {{ old('expedido', $funcionario->expedido ?? '') == $dep ? 'selected' : '' }}>{{ $dep }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="form-group col-md-6">
        <label for="celular">📱 N° Celular</label>
        <input type="text" name="celular" class="form-control" 
               value="{{ old('celular', $funcionario->celular ?? '') }}" 
               oninput="soloNumeros(this)">
    </div>

    <div class="form-group col-md-6">
        <label for="correo">✉️ Correo <span class="text-danger">*</span></label>
        <input type="email" name="correo" id="correo" class="form-control" 
               value="{{ old('correo', $funcionario->correo ?? '') }}" required>
        <small id="correoError" class="text-danger d-none">El correo debe terminar en <strong>@gmail.com</strong></small>
    </div>
</div>

<div class="row">
    <div class="form-group col-md-6">
        <label>⚧️ Género</label>
        <div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="genero" id="generoM" value="1"
                    {{ old('genero', $funcionario->genero ?? '') == 1 ? 'checked' : '' }} required>
                <label class="form-check-label" for="generoM">👨 M</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="genero" id="generoF" value="2"
                    {{ old('genero', $funcionario->genero ?? '') == 2 ? 'checked' : '' }} required>
                <label class="form-check-label" for="generoF">👩 F</label>
            </div>
        </div>
    </div>

    <div class="form-group col-md-6">
        <label for="cargo">💼 Cargo</label>
        <select name="cargo" class="form-control" required>
            @if(auth()->user()->rol === 'ADMINISTRADOR')
                <option value="ADMINISTRADOR" {{ old('cargo', $funcionario->cargo ?? '') == 'ADMINISTRADOR' ? 'selected' : '' }}>ADMINISTRADOR</option>
                <option value="JEFE" {{ old('cargo', $funcionario->cargo ?? '') == 'JEFE' ? 'selected' : '' }}>JEFE</option>
                <option value="TECNICO" {{ old('cargo', $funcionario->cargo ?? '') == 'TECNICO' ? 'selected' : '' }}>TECNICO</option>
            @elseif(auth()->user()->rol === 'JEFE')
                <option value="JEFE" {{ old('cargo', $funcionario->cargo ?? '') == 'JEFE' ? 'selected' : '' }}>JEFE</option>
                <option value="TECNICO" {{ old('cargo', $funcionario->cargo ?? '') == 'TECNICO' ? 'selected' : '' }}>TECNICO</option>
            @endif
        </select>
    </div>
</div>

<div class="row">
    <div class="form-group col-md-6">
        <label for="fecha_nacimiento">🎂 Fecha de Nacimiento</label>
        <input type="date" name="fecha_nacimiento" class="form-control"
               value="{{ old('fecha_nacimiento', $funcionario->fecha_nacimiento ?? '') }}" required>
    </div>
    <div class="form-group col-md-6">
        <label for="fecha_registro">🗓️ Fecha de Registro</label>
        <input type="date" name="fecha_registro" class="form-control"
               value="{{ old('fecha_registro', $funcionario->fecha_registro ?? (isset($funcionario->fecha_registro) ? $funcionario->fecha_registro : now()->toDateString())) }}" readonly>
    </div>
</div>

{{-- JavaScript al final del formulario --}}
<script>
    function capitalizar(input) {
        let palabras = input.value.toLowerCase().split(" ");
        for (let i = 0; i < palabras.length; i++) {
            if (palabras[i].length > 0) {
                palabras[i] = palabras[i][0].toUpperCase() + palabras[i].slice(1);
            }
        }
        input.value = palabras.join(" ");
    }

    function soloNumeros(input) {
        input.value = input.value.replace(/\D/g, '');
    }

    function soloLetrasMayusculas(input) {
        input.value = input.value.replace(/[^A-Z]/g, '').toUpperCase();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const correoInput = document.getElementById('correo');
        const correoError = document.getElementById('correoError');

        correoInput.addEventListener('blur', function () {
            const correo = correoInput.value.trim();
            if (!correo.endsWith('@gmail.com')) {
                correoError.classList.remove('d-none');
                correoInput.classList.add('is-invalid');
                correoInput.focus();
            } else {
                correoError.classList.add('d-none');
                correoInput.classList.remove('is-invalid');
            }
        });

        // Si el campo fecha_registro existe y está vacío, poner la fecha de hoy
        const fechaRegistro = document.querySelector('input[name="fecha_registro"]');
        if (fechaRegistro && !fechaRegistro.value) {
            const hoy = new Date();
            const yyyy = hoy.getFullYear();
            const mm = String(hoy.getMonth() + 1).padStart(2, '0');
            const dd = String(hoy.getDate()).padStart(2, '0');
            fechaRegistro.value = `${yyyy}-${mm}-${dd}`;
        }
    });
</script>

<style>
    .is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220,53,69,.25);
    }
</style>