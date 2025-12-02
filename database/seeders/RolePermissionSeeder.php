<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar datos anteriores
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ---------------------
        // 📌 CREAR PERMISOS
        // ---------------------
        $permissions = [
            'ver todos los proyectos',
            'crear proyectos',
            'editar proyectos',
            'clasificar proyectos',
            'asignar técnicos a proyectos',
            'ver desempeño',
            'evaluar desempeño',
            'crear funcionarios',
            'editar funcionarios',
            'ver funcionarios',
            'ver asistencia',
            'ver ubicación',
            'ver reportes consolidados',
            'ver proyectos asignados',
            'registrar salida a inspección',
            'registrar llegada de inspección',
            'subir fotografía inspección',
            'registrar asistencia',
            'ver historial propio',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ---------------------
        // 🎖️ ROLES
        // ---------------------

        // JEFE
        $admin = Role::firstOrCreate(['name' => 'ADMINISTRADOR']);
        $admin->syncPermissions([
            'ver todos los proyectos',
            'crear proyectos',
            'editar proyectos',
            'clasificar proyectos',
            'asignar técnicos a proyectos',
            'ver desempeño',
            'evaluar desempeño',
            'crear funcionarios',
            'editar funcionarios',
            'ver funcionarios',
            'ver asistencia',
            'ver ubicación',
            'ver reportes consolidados',
        ]);

        // ADMINISTRADOR
        $jefe = Role::firstOrCreate(['name' => 'JEFE']);
        $jefe->syncPermissions([
            'ver todos los proyectos',
            'asignar técnicos a proyectos',
            'ver desempeño',
            'evaluar desempeño',
            'crear funcionarios',
            'editar funcionarios',
            'ver funcionarios',
            'ver asistencia',
            'ver ubicación',
            'clasificar proyectos',
        ]);

        // TÉCNICO
        $tecnico = Role::firstOrCreate(['name' => 'TECNICO']);
        $tecnico->syncPermissions([
            'ver proyectos asignados',
            'registrar salida a inspección',
            'registrar llegada de inspección',
            'subir fotografía inspección',
            'registrar asistencia',
            'ver historial propio',
        ]);
    }
}
