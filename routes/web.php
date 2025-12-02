<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\InspeccionController;
use App\Http\Controllers\DesempeñoController;
use App\Http\Controllers\FuncionarioController;

// Página de inicio: login
// Route::get('/', [ProyectoController::class, 'home'])->name('home'); // <-- comenta o elimina esta línea

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    Route::get('/', [ProyectoController::class, 'home'])->name('home');
    // AGREGAR DETALLE DE INSPECCIÓN (independiente del flujo)
    Route::post('/funcionario/ubicacion/actual', [InspeccionController::class, 'actualizarUbicacionActual'])->name('funcionario.ubicacion.actual');
});

// Autenticación (login, register, etc.)
Auth::routes();

// Ruta después de login
Route::get('/home', [App\Http\Controllers\ProyectoController::class, 'home'])->name('dashboard');

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    // AGREGAR DETALLE DE INSPECCIÓN (independiente del flujo)
    Route::post('inspecciones/{inspeccion}/agregar-detalle/{funcionario}', [InspeccionController::class, 'agregarDetalle'])->name('inspecciones.agregarDetalle');
    // Subir ubicación actual del funcionario en una inspección (para el mapa)
    Route::post('inspecciones/{inspeccion}/{funcionario}/subir-ubicacion', [InspeccionController::class, 'subirUbicacion'])->name('inspecciones.subirUbicacion');
    // Obtener datos actuales de la asignación (ubicaciones/fotos) en JSON
    Route::get('inspecciones/{inspeccion}/{funcionario}/datos-asignacion', [InspeccionController::class, 'datosAsignacion'])->name('inspecciones.datosAsignacion');

    // MÓDULO DE PROYECTOS
    Route::resource('proyectos', ProyectoController::class);
    Route::post('proyectos/update', [ProyectoController::class, 'update'])->name('proyectos.update');
    Route::get('proyectos/concluidos', [ProyectoController::class, 'verConcluidos'])->name('proyectos.concluidos');
    Route::get('proyectos/concluir/{id}', [ProyectoController::class, 'concluir'])->name('proyectos.concluir');
    Route::get('proyectos/desactivados', [ProyectoController::class, 'desactivados'])->name('proyectos.desactivados');
    Route::patch('proyectos/{id}/reactivar', [ProyectoController::class, 'reactivar'])->name('proyectos.reactivar');
    Route::delete('proyectos/{id}/eliminar-definitivo', [ProyectoController::class, 'destroyDefinitivo'])->name('proyectos.eliminarDefinitivo');

    // MÓDULO DE FUNCIONARIOS
    Route::resource('funcionarios', FuncionarioController::class);
    Route::post('mi-perfil/foto', [FuncionarioController::class, 'actualizarFoto'])->name('funcionarios.foto');
    Route::post('mi-perfil/password', [FuncionarioController::class, 'cambiarPassword'])->name('funcionarios.cambiarPassword');
    Route::get('funcionarios-desactivados', [FuncionarioController::class, 'desactivados'])->name('funcionarios.desactivados');
    Route::patch('funcionarios/{id}/reactivar', [FuncionarioController::class, 'reactivar'])->name('funcionarios.reactivar');
    Route::patch('funcionarios/{id}/desactivar', [FuncionarioController::class, 'desactivar'])->name('funcionarios.desactivar');

    // OTROS MÓDULOS
    Route::get('/asistencias', [AsistenciaController::class, 'index'])->name('asistencias');

    // INSPECCIONES
    Route::get('/inspecciones', [InspeccionController::class, 'index'])->name('inspecciones');
    Route::post('inspecciones/{inspeccion}/aceptar/{funcionario}', [InspeccionController::class, 'aceptar'])->name('inspecciones.aceptar');
    // Rechazar asignación (cuando un funcionario rechaza la inspección asignada)
    Route::post('inspecciones/{inspeccion}/rechazar/{funcionario}', [InspeccionController::class, 'rechazar'])->name('inspecciones.rechazar');
    Route::post('inspecciones/{inspeccion}/marcar-llegada/{funcionario}', [InspeccionController::class, 'marcarLlegada'])->name('inspecciones.marcarLlegada');
    Route::post('inspecciones/{inspeccion}/marcar-salida/{funcionario}', [InspeccionController::class, 'marcarSalida'])->name('inspecciones.marcarSalida');
    Route::post('inspecciones/{inspeccion}/marcar-llegada-gamea/{funcionario}', [InspeccionController::class, 'marcarLlegadaGamea'])->name('inspecciones.marcarLlegadaGamea');
    Route::post('inspecciones/{inspeccion}/subir-fotos/{funcionario}', [InspeccionController::class, 'subirFotos'])->name('inspecciones.subirFotos');
    Route::post('inspecciones/{inspeccion}/eliminar-foto/{funcionario}', [InspeccionController::class, 'eliminarFoto'])->name('inspecciones.eliminarFoto');
    Route::post('inspecciones/{inspeccion}/subir-fotos/{funcionario}', [InspeccionController::class, 'subirFotos'])->name('inspecciones.subirFotos');
    Route::resource('inspecciones', InspeccionController::class)->except(['index']);


    // Desempeño
    Route::get('desempeño', [DesempeñoController::class, 'index'])->name('desempeño.index');
    Route::get('desempeño/funcionario/{id}', [DesempeñoController::class, 'showFuncionario'])->name('desempeño.funcionario');

    Route::get('/desempeño', [DesempeñoController::class, 'index'])->name('desempeño.index');
    // Perfil usuario
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.show');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [App\Http\Controllers\ProfileController::class, 'photo'])->name('profile.photo');

    Route::post('/funcionario/ubicacion/actual', [InspeccionController::class, 'actualizarUbicacionActual'])->name('funcionario.ubicacion.actual');
    // Historial
    Route::get('historial', [App\Http\Controllers\HistorialController::class, 'index'])->name('historial.index');
    Route::get('historial/inspeccion-pdf/{id}', [App\Http\Controllers\HistorialController::class, 'inspeccionPdf'])->name('historial.inspeccion.pdf');
    Route::get('historial/reporte/{id}', [App\Http\Controllers\HistorialController::class, 'reporteFuncionario'])->name('historial.reporte');
    Route::get('historial/detalles/{id}', [App\Http\Controllers\HistorialController::class, 'detallesFuncionario'])->name('historial.detalles');

    // Auditoria
    Route::get('auditoria', [App\Http\Controllers\AuditoriaController::class, 'index'])->name('auditoria.index');
    Route::get('auditoria/usuario/{userId}', [App\Http\Controllers\AuditoriaController::class, 'usuarioDetalles'])->name('auditoria.usuario.detalles');
    Route::post('auditoria/acknowledge/{userId}', [App\Http\Controllers\AuditoriaController::class, 'acknowledgeAlert'])->name('auditoria.acknowledge');
});

// Tutoriales
Route::get('/tutoriales', function () {
    return view('tutoriales.index');
})->middleware('auth')->name('tutoriales');

