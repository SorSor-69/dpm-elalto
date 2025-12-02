<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use Illuminate\Http\Request;

class ProyectoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $proyectos = Proyecto::where('activo', true)
            ->when($search, function ($query, $search) {
                $query->where('nombre', 'like', "%$search%")
                    ->orWhere('descripcion', 'like', "%$search%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('proyectos.index', compact('proyectos'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $rolUsuario = property_exists($user, 'rol') ? $user->rol : '';
        // TECNICO no puede crear proyectos
        if ($rolUsuario === 'TECNICO') {
            return back()->with('error', 'No tienes permisos para registrar proyectos.');
        }
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'distrito' => 'required|string|max:100',
            'presupuesto' => 'required|numeric',
        ]);

        Proyecto::create([
            'nombre' => strtoupper($request->nombre),
            'descripcion' => strtoupper($request->descripcion ?? ''),
            'latitud' => $request->latitud,
            'longitud' => $request->longitud,
            'distrito' => strtoupper($request->distrito),
            'presupuesto' => $request->presupuesto,
            'estado' => 'NUEVO',
            'fecha_creacion' => now()->toDateString(),
            'hora_creacion' => now()->toTimeString(),
            'activo' => true,
        ]);

        return redirect()->route('proyectos.index')->with('success', 'Proyecto creado exitosamente.');
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $rolUsuario = property_exists($user, 'rol') ? $user->rol : '';
        if ($rolUsuario === 'TECNICO') {
            return back()->with('error', 'No tienes permisos para actualizar proyectos.');
        }
        $request->validate([
            'id' => 'required|exists:proyectos,id',
            'nombre' => 'required|string|max:255',
            'distrito' => 'required|string|max:100',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'presupuesto' => 'nullable|numeric|min:0',
        ]);

        $proyecto = Proyecto::findOrFail($request->id);

        $proyecto->nombre = strtoupper($request->nombre);
        $proyecto->distrito = strtoupper($request->distrito);
        $proyecto->latitud = $request->latitud;
        $proyecto->longitud = $request->longitud;

        // Sumar presupuesto si se ingresó valor nuevo
        if ($request->filled('presupuesto')) {
            $proyecto->presupuesto += $request->presupuesto;
        }

        $proyecto->save();

        return redirect()->route('proyectos.index')->with('success', 'Proyecto actualizado exitosamente.');
    }

    public function destroy(Proyecto $proyecto)
    {
        $proyecto->update([
            'activo' => false,
            'fecha_desactivacion' => now(),
        ]);

        return redirect()->route('proyectos.index')->with('success', 'Proyecto desactivado correctamente.');
    }

    public function reactivar($id)
    {
        $proyecto = Proyecto::findOrFail($id);
        $proyecto->update([
            'activo' => true,
            'estado' => 'EN PROCESO', // Al reactivar, el estado pasa a EN PROCESO
            'fecha_desactivacion' => null,
        ]);

        return redirect()->route('proyectos.index')->with('success', 'Proyecto reactivado correctamente.');
    }

    public function desactivados(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $proyectos = Proyecto::where('activo', false)
            ->when($search, function ($query, $search) {
                $query->where('nombre', 'like', "%$search%")
                    ->orWhere('descripcion', 'like', "%$search%");
            })
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);

        return view('proyectos.desactivados', compact('proyectos'));
    }

    public function destroyDefinitivo($id)
    {
        $proyecto = Proyecto::findOrFail($id);
        $proyecto->delete();

        return redirect()->route('proyectos.desactivados')->with('success', 'Proyecto eliminado definitivamente.');
    }

    public function concluir($id)
    {
        $proyecto = Proyecto::findOrFail($id);
        $proyecto->estado = 'CONCLUIDO';
        $proyecto->fecha_conclusion = now(); // Asignar fecha de conclusión actual
        $proyecto->save();

        return redirect()->route('proyectos.index')->with('success', 'Proyecto marcado como concluido.');
    }

    // (Opcional) Ver proyectos concluidos
    public function verConcluidos(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $proyectos = Proyecto::where('estado', 'CONCLUIDO')
            ->when($search, function ($query, $search) {
                $query->where('nombre', 'like', "%$search%")
                    ->orWhere('descripcion', 'like', "%$search%");
            })
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);

        return view('proyectos.concluidos', compact('proyectos'));
    }

    public function show(Proyecto $proyecto)
    {
        return view('proyectos.show', compact('proyecto'));
    }

    public function home(Request $request)
    {
        // Si el usuario es JEFE o TECNICO, redirigir directamente al módulo de inspecciones
        $user = auth()->user();
        $rolUsuario = property_exists($user, 'rol') ? $user->rol : '';
        
        \Illuminate\Support\Facades\Log::info('ProyectoController::home - INICIO', [
            'user_id' => $user->id ?? null,
            'rol_user_property' => $rolUsuario
        ]);
        
        // Si no está en User, buscar en Funcionario
        if (empty($rolUsuario)) {
            try {
                $funcionario = \App\Models\Funcionario::where('user_id', $user->id)->first();
                if ($funcionario) {
                    // El formulario guarda el cargo en 'cargo'. Usar 'rol' si existe, si no usar 'cargo'.
                    $rolUsuario = !empty($funcionario->rol) ? $funcionario->rol : (!empty($funcionario->cargo) ? $funcionario->cargo : '');
                    \Illuminate\Support\Facades\Log::info('ProyectoController::home - Rol encontrado en Funcionario', [
                        'funcionario_rol' => $rolUsuario
                    ]);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('ProyectoController::home - Error buscando Funcionario', ['error' => $e->getMessage()]);
            }
        }
        
        // Convertir a mayúscula para comparación
        $rolUsuario = strtoupper((string)$rolUsuario);
        
        \Illuminate\Support\Facades\Log::info('ProyectoController::home - Rol final', [
            'rol_upper' => $rolUsuario
        ]);
        
        if (in_array($rolUsuario, ['JEFE', 'TECNICO'])) {
            \Illuminate\Support\Facades\Log::info('ProyectoController::home - REDIRIGIENDO A INSPECCIONES');
            return redirect()->route('inspecciones');
        }
        
        \Illuminate\Support\Facades\Log::info('ProyectoController::home - Mostrando home normal');

        // Lista fija de distritos
        $distritos = [
            'D-1', 'D-2', 'D-3', 'D-4', 'D-5', 'D-6', 'D-7', 'D-8', 'D-9', 'D-10', 'D-11', 'D-12', 'D-13', 'D-14'
        ];

        $selectedDistritos = $request->get('distrito', []);
        if (!is_array($selectedDistritos)) {
            $selectedDistritos = [$selectedDistritos];
        }

        $proyectos = \App\Models\Proyecto::where('activo', true)
            ->when(count($selectedDistritos) > 0, function ($query) use ($selectedDistritos) {
                $query->whereIn('distrito', $selectedDistritos);
            })
            ->get();

        $proyectosPorDistrito = collect($proyectos)
            ->groupBy('distrito')
            ->map(function ($items, $key) {
                return (object)[
                    'distrito' => $key,
                    'total' => $items->count()
                ];
            })
            ->values();

        $totalProyectos = $proyectos->count();

        return view('home', compact('distritos', 'selectedDistritos', 'proyectosPorDistrito', 'totalProyectos', 'proyectos'));
    }
}