<?php

namespace App\Http\Controllers;

use App\Models\Inspeccion;
use App\Models\Funcionario;
use App\Models\FuncionarioInspeccion;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InspeccionController extends Controller
{    // Agregar detalle de inspección (NO cambia el estado de flujo)
    public function agregarDetalle(Request $request, $inspeccion_id, $funcionario_id)
    {
        // DEPURACIÓN: Verifica que llegan los datos esperados
        // Si quieres ver en el navegador, descomenta la siguiente línea:
        // dd($request->all());

        $asignacion = FuncionarioInspeccion::where('inspeccion_id', $inspeccion_id)
            ->where('funcionario_id', $funcionario_id)
            ->firstOrFail();

        $request->validate([
            'detalle_inspeccion' => 'required|string|max:200',
        ]);

        // Asegura que el campo esté casteado como array en el modelo
        $detalles = $asignacion->detalle_inspeccion;
        if (empty($detalles) || !is_array($detalles)) {
            $detalles = [];
        }
        $detalles[] = [
            'detalle' => $request->detalle_inspeccion,
            'fecha' => now()->format('Y-m-d H:i:s'),
        ];
        $asignacion->detalle_inspeccion = $detalles;
        // Si tienes los campos latitud_detalle y longitud_detalle en la tabla, guárdalos
        if ($request->filled('latitud')) {
            $asignacion->latitud_detalle = $request->latitud;
        }
        if ($request->filled('longitud')) {
            $asignacion->longitud_detalle = $request->longitud;
        }
        $asignacion->save();

        if ($request->ajax()) {
            $inspeccion = Inspeccion::with(['proyecto', 'funcionarios.funcionario'])
                ->findOrFail($inspeccion_id);
            $f = $asignacion;
            $esMiFila = true;
            return view('inspecciones._modal_detalle', compact('inspeccion', 'f', 'esMiFila'))->render();
        }
        return back()->with('success', 'Detalle agregado correctamente.');
    }

    public function index()
    {
        // Traer todos los datos relevantes del proyecto y de la inspección
        $user = auth()->user();
        // Si el usuario tiene relación de roles (spatie/laravel-permission)
        if (method_exists($user, 'getRoleNames')) {
            $rol = $user->getRoleNames()->first() ?? '';
        } elseif (property_exists($user, 'rol')) {
            $rol = $user->rol;
        } else {
            $rol = '';
        }

        // Obtener el funcionario asociado (si existe) para cualquier rol
        $funcionario = Funcionario::where('user_id', $user->id)->first();
        $funcionarioId = $funcionario ? (string) $funcionario->id : null;

        // Obtener inspecciones según rol del usuario
        $query = Inspeccion::with([
            'proyecto',
            'funcionarios.funcionario:id,nombres,apellidos'
        ])->orderByDesc('id');

        if ($rol === 'JEFE') {
            // JEFE ve todas las inspecciones excepto las que tengan algún funcionario con CARGO ADMINISTRADOR
            $query->whereDoesntHave('funcionarios', function($q) {
                $q->where('rol_en_inspeccion', 'ADMINISTRADOR');
            });
        } elseif ($rol === 'TECNICO') {
            // TECNICO solo ve sus propias asignaciones
            if ($funcionarioId) {
                $query->whereHas('funcionarios', function($q) use ($funcionarioId) {
                    $q->where('funcionario_id', $funcionarioId);
                });
            } else {
                // Si no tiene funcionario_id, no ver nada
                $query->whereRaw('1 = 0');
            }
        }

        $inspecciones = $query->paginate(100);

        $proyectos = Proyecto::all();
        $funcionarios = Funcionario::all();

        // Mis Inspecciones: traer todas las inspecciones asignadas al funcionario autenticado
        $misInspecciones = collect();
        // Colección de asignaciones (FuncionarioInspeccion) para mostrar en la vista
        $misAsignaciones = collect();
        // Si existe funcionario asociado, traer todas sus asignaciones (sin filtrar por rol)
        if ($funcionarioId) {
            // Obtener los ids de inspeccion desde la tabla intermedia (forzando strings)
            $inspeccionIds = FuncionarioInspeccion::where('funcionario_id', $funcionarioId)
                ->pluck('inspeccion_id')
                ->map(function($id){ return (string)$id; })
                ->unique()
                ->values()
                ->toArray();

            if (!empty($inspeccionIds)) {
                // Traer las inspecciones por esos ids de forma robusta
                $misInspecciones = Inspeccion::with(['proyecto', 'funcionarios.funcionario:id,nombres,apellidos'])
                    ->whereIn('_id', $inspeccionIds)
                    ->orderByDesc('fecha')
                    ->get();
            }

            // Además, traer las asignaciones concretas (FuncionarioInspeccion) para renderizado directo
            $misAsignaciones = FuncionarioInspeccion::with(['inspeccion.proyecto', 'funcionario'])
                ->where('funcionario_id', $funcionarioId)
                ->get();

            // Ordenar las asignaciones por la fecha de la inspección relacionada (más nuevo -> más antiguo)
            $misAsignaciones = $misAsignaciones->sortByDesc(function($a) {
                try {
                    // Intentar usar el campo 'fecha' de la inspección si existe
                    if (isset($a->inspeccion) && isset($a->inspeccion->fecha) && $a->inspeccion->fecha) {
                        $ts = is_numeric($a->inspeccion->fecha) ? (int)$a->inspeccion->fecha : strtotime((string)$a->inspeccion->fecha);
                        return $ts ?: 0;
                    }
                    // Fallback a created_at de la asignación
                    if (isset($a->created_at) && $a->created_at) {
                        return is_numeric($a->created_at) ? (int)$a->created_at : strtotime((string)$a->created_at);
                    }
                } catch (\Throwable $e) {
                    // ignorar y devolver 0
                }
                return 0;
            })->values();
        }

        // DEBUG: obtener asignaciones directas del funcionario para ver cargos y conteo
        $asignacionesFuncionario = collect();
        $asignacionesCount = 0;
        $rolesAsignados = collect();
        if ($funcionarioId) {
            try {
                $asignacionesFuncionario = FuncionarioInspeccion::where('funcionario_id', $funcionarioId)->get();
                $asignacionesCount = $asignacionesFuncionario->count();
                $rolesAsignados = $asignacionesFuncionario->pluck('rol_en_inspeccion')->unique()->values();
            } catch (\Throwable $e) {
                // ignore DB issues in debug
            }
        }

        // Si el usuario actual tiene un funcionario asociado, ver si tiene asignaciones sin notificar
        try {
            $user = auth()->user();
                $func = Funcionario::where('user_id', $user->id)->first();
            if ($func) {
                    $asignacion = FuncionarioInspeccion::where('funcionario_id', (string)$func->id)
                    ->where(function($q){ $q->whereNull('notificado')->orWhere('notificado', false); })
                    ->first();
                if ($asignacion) {
                    session()->flash('asignacion_nueva', true);
                    session()->flash('inspeccion_id', $asignacion->inspeccion_id);
                    // Also flash which funcionario the assignment belongs to so the
                    // front-end can verify the alert is for the current user.
                    session()->flash('asignacion_funcionario_id', $asignacion->funcionario_id);
                    // marcar como notificado para no mostrar otra vez
                    $asignacion->notificado = true;
                    $asignacion->save();
                }
            }
        } catch (\Throwable $e) {
            // no bloquear la vista por errores de notificación
        }

        // Registro temporal para depuración: verificar por qué 'misAsignaciones' aparece vacío en la vista
        try {
            Log::debug('InspeccionController@index debug', [
                'user_id' => $user->id ?? null,
                'rol' => $rol,
                'funcionarioId' => $funcionarioId,
                'misAsignaciones_count' => is_object($misAsignaciones) ? $misAsignaciones->count() : null,
                'inspeccionIds_sample' => isset($inspeccionIds) ? array_slice($inspeccionIds, 0, 10) : null,
            ]);
        } catch (\Throwable $e) {
            // no bloquear la respuesta por logging
        }

        return view('inspecciones.index', compact('inspecciones', 'proyectos', 'funcionarios', 'misInspecciones', 'misAsignaciones', 'funcionarioId', 'asignacionesCount', 'rolesAsignados'));
    }

    // Guardar nueva inspección
    public function store(Request $request)
    {
        $user = auth()->user();
        if (method_exists($user, 'getRoleNames')) {
            $rolUsuario = $user->getRoleNames()->first() ?? '';
        } else {
            $rolUsuario = $user->rol ?? '';
        }
        try {
            Log::info('InspeccionController@store called', [
                'user_id' => $user->id ?? null,
                'detected_role_property' => property_exists($user, 'rol'),
                'rolUsuario_initial' => $rolUsuario,
                'request_keys' => array_keys($request->all()),
            ]);
        } catch (\Throwable $e) {}
        // TECNICO no puede crear inspecciones
        if ($rolUsuario === 'TECNICO') {
            return back()->with('error', 'No tienes permisos para registrar inspecciones.');
        }
        $rules = [
            'actividad' => 'required|string',
            'tiempo_inspeccion' => 'required|string',
            'funcionarios' => 'required|array|min:1',
        ];
        // Run manual validator so we can log failures for debugging JEFE issue
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            try {
                Log::warning('InspeccionController@store validation failed', [
                    'user_id' => $user->id ?? null,
                    'errors' => $validator->errors()->all(),
                    'request_funcionarios' => $request->input('funcionarios'),
                ]);
            } catch (\Throwable $e) {}
            return back()->withErrors($validator)->withInput();
        }
        if ($request->filled('proyecto_manual')) {
            $rules['proyecto_manual'] = 'required|string|max:100';
        } else {
            $rules['proyecto_id'] = 'required|string';
        }
        $request->validate($rules);

        try {
            if ($request->filled('proyecto_manual')) {
                $inspeccion = \App\Models\Inspeccion::create([
                    'proyecto_manual' => $request->proyecto_manual,
                    'actividad' => $request->actividad,
                    'tiempo_inspeccion' => $request->tiempo_inspeccion,
                    'activo' => 1,
                    'fecha' => now(),
                ]);
                // Guardar quien asignó (si existe funcionario asociado al usuario, usar ese id)
                try {
                    $func = \App\Models\Funcionario::where('user_id', $user->id)->first();
                    if ($func) {
                        $inspeccion->asignado_por = (string) $func->id;
                    } else {
                        $inspeccion->asignado_por = $user->id;
                    }
                    $inspeccion->created_by = $user->id;
                    $inspeccion->save();
                } catch (\Throwable $e) {
                    // no bloquear la creación si esto falla
                }
            } else {
                $proyecto_id = $request->proyecto_id;
                $proyecto = \App\Models\Proyecto::find($proyecto_id);
                if (!$proyecto) {
                    return back()->with('error', 'El proyecto seleccionado no existe.');
                }
                $inspeccion = \App\Models\Inspeccion::create([
                    'proyecto_id' => $proyecto_id,
                    'actividad' => $request->actividad,
                    'tiempo_inspeccion' => $request->tiempo_inspeccion,
                    'activo' => 1,
                    'fecha' => now(),
                ]);
                // Guardar quien asignó (si existe funcionario asociado al usuario, usar ese id)
                try {
                    $func = \App\Models\Funcionario::where('user_id', $user->id)->first();
                    if ($func) {
                        $inspeccion->asignado_por = (string) $func->id;
                    } else {
                        $inspeccion->asignado_por = $user->id;
                    }
                    $inspeccion->created_by = $user->id;
                    $inspeccion->save();
                } catch (\Throwable $e) {
                    // ignore
                }
                // Si se asigna al menos un funcionario, cambiar estado a EN PROCESO
                if (!empty($request->funcionarios)) {
                    $proyecto->estado = 'EN PROCESO';
                    $proyecto->save();
                }
            }

            try {
                Log::info('InspeccionController@store funcionarios payload', ['funcionarios' => $request->input('funcionarios')]);
            } catch (\Throwable $e) {}

            foreach ($request->funcionarios as $rol => $funcionarios) {
                if (is_array($funcionarios)) {
                    foreach ($funcionarios as $funcionario_id) {
                        // Si el usuario es JEFE, no puede asignar a ADMINISTRADOR
                        if ($rolUsuario === 'JEFE') {
                            $funcionario = \App\Models\Funcionario::find($funcionario_id);
                            if ($funcionario && $funcionario->rol === 'ADMINISTRADOR') {
                                continue; // Saltar asignación a ADMINISTRADOR
                            }
                        }
                        \App\Models\FuncionarioInspeccion::create([
                            'funcionario_id' => (string)$funcionario_id,
                            'inspeccion_id' => (string)$inspeccion->id,
                            'rol_en_inspeccion' => $rol,
                            'activo' => 1,
                            'notificado' => false,
                        ]);
                    }
                }
            }

            try {
                $countAsign = \App\Models\FuncionarioInspeccion::where('inspeccion_id', (string)$inspeccion->id)->count();
                Log::info('Inspeccion created', ['inspeccion_id' => (string)$inspeccion->id, 'asignaciones_count' => $countAsign, 'created_by' => $user->id ?? null]);
            } catch (\Throwable $e) {}

            return redirect()->route('inspecciones.index')->with('success', 'Inspeccion Registrada correctamente.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al registrar la inspección: ' . $e->getMessage());
        }
    }

    // Mostrar formulario de edición
    public function edit($id)
    {
        $inspeccion = Inspeccion::with('funcionarios')->findOrFail($id);
        $proyectos = Proyecto::all();
        $funcionarios = Funcionario::all();
        return view('inspecciones.edit', compact('inspeccion', 'proyectos', 'funcionarios'));
    }

    // Actualizar inspección
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $rolUsuario = property_exists($user, 'rol') ? $user->rol : '';
        if ($rolUsuario === 'TECNICO') {
            return back()->with('error', 'No tienes permisos para actualizar inspecciones.');
        }
        $request->validate([
            'proyecto_id' => 'required|exists:proyectos,id',
            'actividad' => 'required|string',
            'tiempo_inspeccion' => 'required|string',
            'funcionarios' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $inspeccion = Inspeccion::findOrFail($id);
            $inspeccion->update([
                'proyecto_id' => $request->proyecto_id,
                'actividad' => $request->actividad,
                'tiempo_inspeccion' => $request->tiempo_inspeccion,
                'observaciones' => $request->observaciones,
                'activo' => 1,
            ]);

            FuncionarioInspeccion::where('inspeccion_id', $inspeccion->id)->delete();

            foreach ($request->funcionarios as $rol => $funcionarios) {
                if (is_array($funcionarios)) {
                    foreach ($funcionarios as $funcionario_id) {
                        FuncionarioInspeccion::create([
                            'funcionario_id' => $funcionario_id,
                            'inspeccion_id' => $inspeccion->id,
                            'rol_en_inspeccion' => $rol,
                            'activo' => 1,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('inspecciones.index')->with('success', 'Inspección actualizada correctamente.');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->with('error', 'Error al actualizar la inspección: ' . $e->getMessage());
        }
    }

    // Eliminar inspección
    public function destroy($id)
    {
        $user = auth()->user();
        $rolUsuario = property_exists($user, 'rol') ? $user->rol : '';
        if ($rolUsuario === 'TECNICO') {
            return back()->with('error', 'No tienes permisos para eliminar inspecciones.');
        }
        $inspeccion = Inspeccion::findOrFail($id);
        FuncionarioInspeccion::where('inspeccion_id', $inspeccion->id)->delete();
        $inspeccion->delete();

        return redirect()->route('inspecciones.index')->with('success', 'Inspección eliminada correctamente.');
    }

    // Salida GAMEA
    public function aceptar(Request $request, $inspeccion_id, $funcionario_id)
    {
        $asignacion = FuncionarioInspeccion::where('inspeccion_id', $inspeccion_id)
            ->where('funcionario_id', $funcionario_id)
            ->firstOrFail();

        $asignacion->hora_salida_gamea = now();
        $asignacion->latitud_salida_gamea = $request->latitud;
        $asignacion->longitud_salida_gamea = $request->longitud;
        $asignacion->save();

        if ($request->ajax()) {
            $inspeccion = Inspeccion::with(['proyecto', 'funcionarios.funcionario'])
                ->findOrFail($inspeccion_id);
            $f = $asignacion;
            $esMiFila = true;
            return view('inspecciones._modal_detalle', compact('inspeccion', 'f', 'esMiFila'))->render();
        }
        return back()->with('success', 'Inspección aceptada. ¡Buen trabajo!');
    }

    /**
     * Rechazar una asignación de inspección por parte del funcionario.
     * Marca la asignación como inactiva (activo = 0).
     */
    public function rechazar(Request $request, $inspeccion_id, $funcionario_id)
    {
        $asignacion = FuncionarioInspeccion::where('inspeccion_id', $inspeccion_id)
            ->where('funcionario_id', $funcionario_id)
            ->firstOrFail();

        // Marcar la asignación como no activa (rechazada)
        $asignacion->activo = 0;
        $asignacion->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Inspección rechazada por el funcionario.']);
        }
        return back()->with('success', 'Inspección rechazada.');
    }

    // Llegada a obra
    public function marcarLlegada(Request $request, $inspeccion_id, $funcionario_id)
    {
        $asignacion = FuncionarioInspeccion::where('inspeccion_id', $inspeccion_id)
            ->where('funcionario_id', $funcionario_id)
            ->firstOrFail();

        $asignacion->hora_llegada_obra = now();
        $asignacion->latitud_llegada_obra = $request->latitud;
        $asignacion->longitud_llegada_obra = $request->longitud;
        if ($request->filled('detalle_inspeccion')) {
            $detalles = $asignacion->detalle_inspeccion ?? [];
            if (!is_array($detalles)) {
                $detalles = [$detalles];
            }
            $detalles[] = [
                'detalle' => $request->detalle_inspeccion,
                'fecha' => now()->format('Y-m-d H:i:s'),
            ];
            $asignacion->detalle_inspeccion = $detalles;
        }
        $asignacion->save();

        if ($request->ajax()) {
            $inspeccion = Inspeccion::with(['proyecto', 'funcionarios.funcionario'])
                ->findOrFail($inspeccion_id);
            $f = $asignacion;
            $esMiFila = true;
            return view('inspecciones._modal_detalle', compact('inspeccion', 'f', 'esMiFila'))->render();
        }
        return back()->with('success', 'Llegada a la obra registrada.');
    }

    // Salida de obra
    public function marcarSalida(Request $request, $inspeccion_id, $funcionario_id)
    {
        $asignacion = FuncionarioInspeccion::where('inspeccion_id', $inspeccion_id)
            ->where('funcionario_id', $funcionario_id)
            ->firstOrFail();

        $asignacion->hora_salida_obra = now();
        $asignacion->latitud_salida_obra = $request->latitud;
        $asignacion->longitud_salida_obra = $request->longitud;
        $asignacion->save();

        if ($request->ajax()) {
            $inspeccion = Inspeccion::with(['proyecto', 'funcionarios.funcionario'])
                ->findOrFail($inspeccion_id);
            $f = $asignacion;
            $esMiFila = true;
            return view('inspecciones._modal_detalle', compact('inspeccion', 'f', 'esMiFila'))->render();
        }
        return back()->with('success', 'Salida de la obra registrada.');
    }

    // Llegada a GAMEA
    public function marcarLlegadaGamea(Request $request, $inspeccion_id, $funcionario_id)
    {
        $asignacion = FuncionarioInspeccion::where('inspeccion_id', $inspeccion_id)
            ->where('funcionario_id', $funcionario_id)
            ->firstOrFail();

        $asignacion->hora_llegada_gamea = now();
        $asignacion->latitud_llegada_gamea = $request->latitud;
        $asignacion->longitud_llegada_gamea = $request->longitud;
        $asignacion->save();

        if ($request->ajax()) {
            $inspeccion = Inspeccion::with(['proyecto', 'funcionarios.funcionario'])
                ->findOrFail($inspeccion_id);
            $f = $asignacion;
            $esMiFila = true;
            return view('inspecciones._modal_detalle', compact('inspeccion', 'f', 'esMiFila'))->render();
        }
        return back()->with('success', 'Llegada al GAMEA registrada.');
    }

    // Subir varias fotos (AJAX o normal)
    public function subirFotos(Request $request, $inspeccion_id, $funcionario_id)
    {
        $request->validate([
            'fotos_llegada_obra' => 'required',
            'fotos_llegada_obra.*' => 'image|max:4096',
        ]);

        $asignacion = FuncionarioInspeccion::where('inspeccion_id', $inspeccion_id)
            ->where('funcionario_id', $funcionario_id)
            ->firstOrFail();
        // No permitir subir más fotos si ya marcó salida de obra
        if (!is_null($asignacion->hora_salida_obra)) {
            return response()->json(['message' => 'No se pueden subir fotos después de la salida de obra.'], 422);
        }

        // Limitar número total de fotos a 5
        $fotos_anteriores = [];
        if ($asignacion->foto_llegada_obra) {
            $fotos_anteriores = json_decode($asignacion->foto_llegada_obra, true) ?? [];
        }
        $nuevas = $request->file('fotos_llegada_obra') ? count($request->file('fotos_llegada_obra')) : 0;
        if (count($fotos_anteriores) + $nuevas > 5) {
            return response()->json(['message' => 'No puedes tener más de 5 fotos.'], 422);
        }
        $paths = [];
        if ($request->hasFile('fotos_llegada_obra')) {
            foreach ($request->file('fotos_llegada_obra') as $foto) {
                // Store in 'imagenes' folder inside the public disk
                $paths[] = $foto->store('imagenes', 'public');
            }
            // Combinar con las fotos anteriores
            $asignacion->foto_llegada_obra = json_encode(array_merge($fotos_anteriores, $paths));
            // Guardar ubicación de la última foto subida
            $asignacion->latitud_foto_llegada_obra = $request->latitud;
            $asignacion->longitud_foto_llegada_obra = $request->longitud;
            $asignacion->save();
        }

        if ($request->ajax()) {
            // Build photos URLs for frontend
            $photos = [];
            $fotosArray = [];
            if ($asignacion->foto_llegada_obra) {
                $fotosArray = json_decode($asignacion->foto_llegada_obra, true) ?? [];
            }
            foreach ($fotosArray as $p) {
                $photos[] = ['url' => asset('storage/' . $p), 'path' => $p];
            }
            $rowId = 'inspeccionRow' . $inspeccion_id . '_' . $asignacion->id;
            return response()->json([
                'success' => true,
                'message' => 'Foto(s) subida(s) correctamente.',
                'photos' => $photos,
                'row_id' => $rowId,
            ]);
        }
        return back()->with('success', 'Foto(s) subida(s) correctamente.');
    }

    // Eliminar una foto por índice
    public function eliminarFoto(Request $request, $inspeccion_id, $funcionario_id)
    {
        $index = $request->input('index');

        $asignacion = FuncionarioInspeccion::where('inspeccion_id', $inspeccion_id)
            ->where('funcionario_id', $funcionario_id)
            ->firstOrFail();

        $fotos = [];
        if ($asignacion->foto_llegada_obra) {
            $fotos = json_decode($asignacion->foto_llegada_obra, true) ?? [];
        }

        if (!isset($fotos[$index])) {
            return response()->json(['message' => 'Foto no encontrada.'], 404);
        }

        // Eliminar archivo del storage si existe (paths are relative to storage/app/public)
        try {
            \Storage::disk('public')->delete($fotos[$index]);
        } catch (\Throwable $e) {
            // don't block if deletion fails
        }

        array_splice($fotos, $index, 1);
        $asignacion->foto_llegada_obra = empty($fotos) ? null : json_encode($fotos);
        $asignacion->save();

        if ($request->ajax()) {
            $photos = [];
            $fotosArray = [];
            if ($asignacion->foto_llegada_obra) {
                $fotosArray = json_decode($asignacion->foto_llegada_obra, true) ?? [];
            }
            foreach ($fotosArray as $p) {
                $photos[] = ['url' => asset('storage/' . $p), 'path' => $p];
            }
            $rowId = 'inspeccionRow' . $inspeccion_id . '_' . $asignacion->id;
            return response()->json([
                'success' => true,
                'message' => 'Foto eliminada.',
                'photos' => $photos,
                'row_id' => $rowId,
            ]);
        }

        return back()->with('success', 'Foto eliminada.');
    }

    // Subir ubicación manual o automática (desde el mapa)
    public function subirUbicacion(Request $request, $inspeccion_id, $funcionario_id)
    {
        $asignacion = FuncionarioInspeccion::where('inspeccion_id', $inspeccion_id)
            ->where('funcionario_id', $funcionario_id)
            ->firstOrFail();

        // Guardar la ubicación actual del funcionario (no marcar salida automáticamente)
        $asignacion->latitud_actual = $request->latitud;
        $asignacion->longitud_actual = $request->longitud;
        $asignacion->save();

        return response()->json(['success' => true]);
    }
    public function actualizarUbicacionActual(Request $request)
    {
        $inspeccionId = $request->input('inspeccion_id');
        $funcionarioId = $request->input('funcionario_id');
        $latitud = $request->input('latitud');
        $longitud = $request->input('longitud');

        // Busca el registro de funcionario en la inspección
        $registro = FuncionarioInspeccion::where('inspeccion_id', $inspeccionId)
            ->where('funcionario_id', $funcionarioId)
            ->first();

        if ($registro) {
            $registro->latitud_actual = $latitud;
            $registro->longitud_actual = $longitud;
            $registro->save();
        }

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Ubicación actual subida correctamente.');
    }

    /**
     * Devuelve en JSON los datos actuales de la asignación (ubicaciones y fotos)
     * para que la vista del mapa pueda obtener la última ubicación cuando la abra
     */
    public function datosAsignacion(Request $request, $inspeccionId, $funcionarioId)
    {
        $registro = FuncionarioInspeccion::where('inspeccion_id', $inspeccionId)
            ->where('funcionario_id', $funcionarioId)
            ->first();

        if (!$registro) {
            return response()->json(['found' => false], 404);
        }

        $fotos = [];
        if ($registro->foto_llegada_obra) {
            $fotos = json_decode($registro->foto_llegada_obra, true) ?? [];
        }

        return response()->json([
            'found' => true,
            'latitud_actual' => $registro->latitud_actual,
            'longitud_actual' => $registro->longitud_actual,
            'latitud_llegada_obra' => $registro->latitud_llegada_obra,
            'longitud_llegada_obra' => $registro->longitud_llegada_obra,
            'latitud_foto_llegada_obra' => $registro->latitud_foto_llegada_obra,
            'longitud_foto_llegada_obra' => $registro->longitud_foto_llegada_obra,
            'latitud_salida_obra' => $registro->latitud_salida_obra,
            'longitud_salida_obra' => $registro->longitud_salida_obra,
            'latitud_salida_gamea' => $registro->latitud_salida_gamea,
            'longitud_salida_gamea' => $registro->longitud_salida_gamea,
            'latitud_llegada_gamea' => $registro->latitud_llegada_gamea,
            'longitud_llegada_gamea' => $registro->longitud_llegada_gamea,
            'fotos' => $fotos,
            'hora_salida_gamea' => $registro->hora_salida_gamea,
            'hora_llegada_obra' => $registro->hora_llegada_obra,
            'hora_salida_obra' => $registro->hora_salida_obra,
            'hora_llegada_gamea' => $registro->hora_llegada_gamea,
        ]);
    }
}
