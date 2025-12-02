<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        \Log::debug('ProfileController@update: request recibido', [
            'all' => $request->all(),
            'password_present' => $request->filled('password'),
        ]);

        try {
            $request->validate([
                'password' => 'nullable|confirmed|min:6',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::debug('ProfileController@update: error de validación', [
                'errors' => $e->errors(),
            ]);
            throw $e;
        }

        $user = Auth::user();

        if (!$user) {
            \Log::debug('ProfileController@update: usuario no autenticado');
            return redirect()->route('profile.show')->with('error', 'No se ha encontrado el usuario autenticado.');
        }
        if ($request->filled('password')) {
            \Log::debug('ProfileController@update: actualizando contraseña');
            $user->password = Hash::make($request->password);
            $user->save();
            return redirect()->route('profile.show')->with('success', 'Contraseña actualizada correctamente.');
        }

        \Log::debug('ProfileController@update: no se realizaron cambios');
        return redirect()->route('profile.show')->with('success', 'No se realizaron cambios.');
    }
    public function photo(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('profile.show')->with('error', 'No se ha encontrado el usuario autenticado.');
        }
        $request->validate([
            'foto_perfil' => 'required|image|max:4096',
        ]);
        if ($user->funcionario) {
            $file = $request->file('foto_perfil');
            $path = $file->store('perfil', 'public');
            $funcionario = $user->funcionario;
            $funcionario->foto_perfil = $path;
            $funcionario->save();
            return redirect()->route('profile.show')->with('success', 'Foto de perfil actualizada correctamente.');
        }
        return redirect()->route('profile.show')->with('error', 'No se encontró el funcionario asociado.');
    }
}