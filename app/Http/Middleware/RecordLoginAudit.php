<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LoginAudit;

class RecordLoginAudit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Registrar el login si el usuario está autenticado y acaba de iniciar sesión
        if (Auth::check() && $request->route()->getName() === 'login') {
            $user = Auth::user();
            $ipAddress = $request->ip();
            $userAgent = $request->header('User-Agent');

            LoginAudit::recordLogin($user, $ipAddress, $userAgent);
        }

        return $response;
    }
}
