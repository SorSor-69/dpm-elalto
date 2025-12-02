<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class FuncionarioController extends Controller
{
    // Actualizar foto de perfil
    public function actualizarFoto(Request $request)
    {
        $request->validate([
            'foto_perfil' => 'required|image|max:4096',
        ]);
        $funcionario = auth()->user()->funcionario;
        if ($request->hasFile('foto_perfil')) {
            $path = $request->file('foto_perfil')->store('perfiles', 'public');
            $funcionario->foto_perfil = $path;
            $funcionario->save();
        }
        return back()->with('success', 'Foto de perfil actualizada correctamente.');
    }

    // Cambiar contraseña
    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);
        $user = auth()->user();
        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->save();
        return back()->with('success', 'Contraseña actualizada correctamente.');
    }
    public function __construct()
    {
        // Middlewares de roles eliminados para compatibilidad con MongoDB
    }

    public function index()
    {
        $funcionario = auth()->user()->funcionario;
        $miCargo = $funcionario ? $funcionario->cargo : null;

        if ($miCargo === 'TECNICO') {
            $funcionarios = Funcionario::orderBy('activo', 'desc')
                ->where('cargo', 'TECNICO')
                ->orderBy('cargo', 'asc')
                ->orderBy('nombres', 'asc')
                ->get();
        } else {
            $funcionarios = Funcionario::orderBy('activo', 'desc')
                ->orderBy('cargo', 'asc')
                ->orderBy('nombres', 'asc')
                ->get();
        }

        return view('funcionarios.index', compact('funcionarios'));
    }

    public function create()
    {
        // Solo ADMINISTRADOR y JEFE pueden crear funcionarios
        if (!in_array(auth()->user()->rol, ['ADMINISTRADOR', 'JEFE'])) {
            abort(403, 'No tienes permiso para crear funcionarios.');
        }
        return view('funcionarios.create');
    }

    public function store(Request $request)
    {
        // Solo ADMINISTRADOR y JEFE pueden crear funcionarios
        if (!in_array(auth()->user()->rol, ['ADMINISTRADOR', 'JEFE'])) {
            abort(403, 'No tienes permiso para crear funcionarios.');
        }
        // JEFE no puede crear ADMINISTRADOR
        if (auth()->user()->rol === 'JEFE' && $request->cargo === 'ADMINISTRADOR') {
            abort(403, 'No tienes permiso para crear funcionarios ADMINISTRADOR.');
        }
        $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'correo' => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/', 'unique:users,email'],
            'ci' => 'required|digits_between:5,15',
            'expedido' => 'required|alpha|size:2',
            'celular' => ['nullable','digits_between:7,10','unique:funcionarios,celular'],
            'cargo' => 'required|in:ADMINISTRADOR,JEFE,TECNICO',
            'genero' => 'required|in:1,2',
            'fecha_nacimiento' => 'required|date',
        ]);

        $password = $request->ci;


        $user = User::create([
            'name' => $request->nombres . ' ' . $request->apellidos,
            'email' => $request->correo,
            'password' => Hash::make($password),
            'rol' => $request->cargo,
        ]);

        $funcionario = Funcionario::create([
            'user_id' => $user->id,
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'correo' => $request->correo,
            'password' => $password, // solo referencia
            'cargo' => $request->cargo,
            'ci' => $request->ci,
            'complemento' => $request->complemento ?? '',
            'expedido' => strtoupper($request->expedido),
            'celular' => $request->celular,
            'genero' => $request->genero,
            'activo' => true,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'fecha_registro' => now()->toDateString(),
        ]);

        return redirect()->route('funcionarios.index')->with('success', 'Funcionario registrado correctamente.');
    }

    public function edit(Funcionario $funcionario) {
        // Solo ADMINISTRADOR puede editar cualquier funcionario
        // JEFE solo puede editar JEFE y TECNICO
        if (auth()->user()->rol === 'JEFE' && !in_array($funcionario->cargo, ['JEFE', 'TECNICO'])) {
            abort(403, 'No tienes permiso para editar este funcionario.');
        }
        if (auth()->user()->rol === 'TECNICO') {
            abort(403, 'No tienes permiso para editar funcionarios.');
        }
        return view('funcionarios._modal_edit', compact('funcionario'));
    }

    public function update(Request $request, Funcionario $funcionario) {
        // Solo ADMINISTRADOR puede actualizar cualquier funcionario
        // JEFE solo puede actualizar JEFE y TECNICO
        if (auth()->user()->rol === 'JEFE' && !in_array($funcionario->cargo, ['JEFE', 'TECNICO'])) {
            abort(403, 'No tienes permiso para actualizar este funcionario.');
        }
        if (auth()->user()->rol === 'TECNICO') {
            abort(403, 'No tienes permiso para actualizar funcionarios.');
        }
        // JEFE no puede cambiar el cargo a ADMINISTRADOR
        if (auth()->user()->rol === 'JEFE' && $request->cargo === 'ADMINISTRADOR') {
            abort(403, 'No tienes permiso para asignar el cargo ADMINISTRADOR.');
        }
        $datosAntes = $funcionario->toArray();
        $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'correo' => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/', 'unique:users,email,' . $funcionario->user_id],
            'ci' => 'required|digits_between:5,15',
            'expedido' => 'required|alpha|size:2',
            'celular' => ['nullable','digits_between:7,10','unique:funcionarios,celular,' . $funcionario->id],
            'password' => 'nullable|string|min:6',
            'cargo' => 'required|in:ADMINISTRADOR,JEFE,TECNICO',
            'genero' => 'required|in:1,2',
            'fecha_nacimiento' => 'required|date',
        ]);

        $user = User::find($funcionario->user_id);
        if ($user) {
            $user->name = $request->nombres . ' ' . $request->apellidos;
            $user->email = $request->correo;
            $user->rol = $request->cargo;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();
        }

        $funcionario->update([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'correo' => $request->correo,
            'cargo' => $request->cargo,
            'ci' => $request->ci,
            'complemento' => $request->complemento ?? '',
            'expedido' => strtoupper($request->expedido),
            'celular' => $request->celular,
            'genero' => $request->genero,
            'password' => $request->filled('password') ? $request->password : $funcionario->password,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            // 'fecha_registro' NO se actualiza aquí para mantener la fecha original
        ]);

        return redirect()->route('funcionarios.index')->with('success', 'Funcionario actualizado correctamente.');
    }

    public function desactivar($id) {
        $funcionario = Funcionario::findOrFail($id);
        // Solo ADMINISTRADOR puede desactivar cualquier funcionario
        // JEFE solo puede desactivar JEFE y TECNICO
        if (auth()->user()->rol === 'JEFE' && !in_array($funcionario->cargo, ['JEFE', 'TECNICO'])) {
            abort(403, 'No tienes permiso para desactivar este funcionario.');
        }
        if (auth()->user()->rol === 'TECNICO') {
            abort(403, 'No tienes permiso para desactivar funcionarios.');
        }
        $datosAntes = $funcionario->toArray();
        $funcionario->activo = false;
        $funcionario->save();

        return redirect()->route('funcionarios.index')->with('success', 'Funcionario desactivado correctamente.');
    }

    public function reactivar($id) {
        $funcionario = Funcionario::findOrFail($id);
        // Solo ADMINISTRADOR puede reactivar cualquier funcionario
        // JEFE solo puede reactivar JEFE y TECNICO
        if (auth()->user()->rol === 'JEFE' && !in_array($funcionario->cargo, ['JEFE', 'TECNICO'])) {
            abort(403, 'No tienes permiso para reactivar este funcionario.');
        }
        if (auth()->user()->rol === 'TECNICO') {
            abort(403, 'No tienes permiso para reactivar funcionarios.');
        }
        $datosAntes = $funcionario->toArray();
        $funcionario->activo = true;
        $funcionario->save();

        return redirect()->route('funcionarios.index')->with('success', 'Funcionario reactivado correctamente.');
    }
}