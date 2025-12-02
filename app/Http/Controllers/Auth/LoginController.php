<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\Funcionario;
use App\Models\FuncionarioInspeccion;
use App\Models\LoginAudit;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * After a user is authenticated, redirect based on role and set notifications for assigned inspections.
     */
    protected function authenticated(\Illuminate\Http\Request $request, $user)
    {
        // Registrar el login en la tabla login_audits
        try {
            $ipAddress = $request->ip();
            $userAgent = $request->header('User-Agent');
            LoginAudit::recordLogin($user, $ipAddress, $userAgent);
        } catch (\Throwable $e) {
            Log::error('LoginAudit::recordLogin failed', ['error' => $e->getMessage()]);
        }

        // Determine role (supporting spatie roles or simple 'rol' field)
        $role = '';
        if (method_exists($user, 'getRoleNames')) {
            $role = $user->getRoleNames()->first() ?? '';
        } elseif (property_exists($user, 'rol')) {
            $role = $user->rol ?? '';
        }

        // If role is not found in User, try to get it from Funcionario
        if (empty($role)) {
            try {
                // First try by user_id
                $func = Funcionario::where('user_id', $user->id)->first();
                if ($func) {
                    // El formulario guarda el cargo en la columna 'cargo'. Usar 'rol' si existe, si no usar 'cargo'.
                    $role = !empty($func->rol) ? $func->rol : (!empty($func->cargo) ? $func->cargo : '');
                }

                // If not found, try by correo (email) in Funcionario
                if (empty($role)) {
                    $funcByEmail = Funcionario::where('correo', $user->email)->first();
                    if ($funcByEmail) {
                        $role = !empty($funcByEmail->rol) ? $funcByEmail->rol : (!empty($funcByEmail->cargo) ? $funcByEmail->cargo : '');
                        $func = $funcByEmail; // keep reference
                    }
                }
            } catch (\Throwable $e) {
                // ignore errors
            }
        }

        // Log entry for debugging: record that authenticated was called and the resolved role
        try {
            Log::info('LoginController::authenticated - INICIO', [
                'user_id' => $user->id ?? null, 
                'role_obtenido' => $role,
                'role_upper' => strtoupper((string)$role)
            ]);
        } catch (\Throwable $e) {
            // ignore logging failures
        }

        // If the user is linked to a Funcionario, check for unnotified assignments and flash a session flag
        try {
            $func = Funcionario::where('user_id', $user->id)->first();
            if ($func) {
                $asignacion = FuncionarioInspeccion::where('funcionario_id', (string)$func->id)
                    ->where(function($q){ $q->whereNull('notificado')->orWhere('notificado', false); })
                    ->first();
                if ($asignacion) {
                    // Flash session so front-end can show a notification after redirect
                    session()->flash('asignacion_nueva', true);
                    session()->flash('inspeccion_id', $asignacion->inspeccion_id);
                    // Also include the funcionario_id to ensure the alert is shown
                    // only for the intended funcionario
                    session()->flash('asignacion_funcionario_id', $asignacion->funcionario_id);
                    $asignacion->notificado = true;
                    $asignacion->save();
                }
            }
        } catch (\Throwable $e) {
            // don't block login on notification errors
        }

        // Redirect JEFE or TECNICO to the inspecciones module
        $roleUpper = strtoupper((string)$role);
        Log::info('LoginController::authenticated - Evaluando rol', ['roleUpper' => $roleUpper]);
        
        if (in_array($roleUpper, ['JEFE', 'TECNICO'])) {
            // Always redirect JEFE and TECNICO to the inspecciones module
            Log::info('LoginController::authenticated - REDIRIGIENDO A INSPECCIONES', ['role' => $roleUpper]);
            return redirect('/inspecciones');
        }

        // Default behavior: go to home for other roles
        Log::info('LoginController::authenticated - REDIRIGIENDO A HOME', ['role' => $roleUpper]);
        return redirect('/home');
    }

    /**
     * Override attemptLogin to ignore "remember me" and always require credentials.
     * This prevents automatic persistent login via the remember cookie.
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt($this->credentials($request), false);
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
