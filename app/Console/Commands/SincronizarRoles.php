<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Funcionario;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SincronizarRoles extends Command
{
    protected $signature = 'sync:roles-funcionarios';
    protected $description = 'Sincroniza los roles de Spatie basados en el campo cargo de los funcionarios';

    public function handle()
    {
        $funcionarios = Funcionario::all();
        $this->info("Sincronizando roles para " . $funcionarios->count() . " funcionarios...");

        foreach ($funcionarios as $funcionario) {
            $rol = strtoupper($funcionario->cargo);

            // Crear el rol si no existe
            if (!Role::where('name', $rol)->exists()) {
                Role::create(['name' => $rol]);
                $this->info("Rol creado: {$rol}");
            }

            // Buscar el usuario y asignarle el rol
            $user = User::find($funcionario->user_id);

            if ($user) {
                $user->syncRoles([$rol]);
                $this->info("Asignado rol {$rol} a {$user->name} ({$user->email})");
            } else {
                $this->warn("No se encontró el usuario con ID: {$funcionario->user_id}");
            }
        }

        $this->info("✅ Roles sincronizados correctamente.");
    }
}
