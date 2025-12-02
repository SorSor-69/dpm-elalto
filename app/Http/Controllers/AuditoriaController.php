<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LoginAudit;
use App\Models\User;
use App\Models\Funcionario;

class AuditoriaController extends Controller
{
    /**
     * Mostrar el panel de auditoría para administradores
     */
    public function index()
    {
        $user = Auth::user();
        
        // Verificar que el usuario sea ADMINISTRADOR
        $role = $this->getUserRole($user);
        if (strtoupper($role) !== 'ADMINISTRADOR') {
            abort(403, 'No tienes permisos para acceder a este modulo.');
        }

        // Obtener todos los accesos sospechosos
        $suspiciousActivities = LoginAudit::getSuspiciousLogins();

        // Obtener accesos recientes de todos los usuarios
        $recentLogins = LoginAudit::with('user')
            ->orderBy('logged_in_at', 'desc')
            ->limit(50)
            ->get();

        return view('auditoria.index', compact('suspiciousActivities', 'recentLogins'));
    }

    /**
     * Mostrar detalles de accesos de un usuario específico
     */
    public function usuarioDetalles($userId)
    {
        $user = Auth::user();
        
        // Verificar que el usuario sea ADMINISTRADOR
        $role = $this->getUserRole($user);
        if (strtoupper($role) !== 'ADMINISTRADOR') {
            abort(403, 'No tienes permisos para acceder a este modulo.');
        }

        $usuario = User::findOrFail($userId);
        $logins = LoginAudit::where('user_id', $userId)
            ->orderBy('logged_in_at', 'desc')
            ->paginate(20);

        // Obtener el funcionario asociado
        $funcionario = Funcionario::where('user_id', $userId)->first();

        return view('auditoria.usuario-detalles', compact('usuario', 'logins', 'funcionario'));
    }

    /**
     * Marcar una alerta de actividad sospechosa como aceptada
     */
    public function acknowledgeAlert($userId)
    {
        $user = Auth::user();
        
        // Verificar que el usuario sea ADMINISTRADOR
        $role = $this->getUserRole($user);
        if (strtoupper($role) !== 'ADMINISTRADOR') {
            return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.');
        }

        // Marcar como aceptado todos los logins sospechosos del usuario
        LoginAudit::where('user_id', $userId)
            ->where(function ($query) {
                $query->where('acknowledged', '!=', true)
                    ->orWhereNull('acknowledged');
            })
            ->update([
                'acknowledged' => true,
                'acknowledged_at' => now()
            ]);

        return redirect()->back()->with('success', 'Alerta marcada como aceptada.');
    }

    /**
     * Obtener el rol del usuario
     */
    private function getUserRole($user)
    {
        $role = '';
        if (method_exists($user, 'getRoleNames')) {
            $role = $user->getRoleNames()->first() ?? '';
        } elseif (property_exists($user, 'rol')) {
            $role = $user->rol ?? '';
        }

        if (empty($role)) {
            try {
                $func = Funcionario::where('user_id', $user->id)->first();
                if ($func) {
                    $role = !empty($func->rol) ? $func->rol : (!empty($func->cargo) ? $func->cargo : '');
                }

                if (empty($role)) {
                    $funcByEmail = Funcionario::where('correo', $user->email)->first();
                    if ($funcByEmail) {
                        $role = !empty($funcByEmail->rol) ? $funcByEmail->rol : (!empty($funcByEmail->cargo) ? $funcByEmail->cargo : '');
                    }
                }
            } catch (\Throwable $e) {
                // ignore errors
            }
        }

        return $role;
    }
}
