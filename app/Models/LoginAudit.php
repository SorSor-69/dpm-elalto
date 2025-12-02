<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class LoginAudit extends Model
{
    use HasFactory;

    // Nombre de la colección en MongoDB
    protected $collection = 'login_audits';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_name',
        'browser',
        'os',
        'logged_in_at',
        'acknowledged',
        'acknowledged_at',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'acknowledged' => 'boolean',
    ];

    /**
     * Relación: un LoginAudit pertenece a un User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Registrar un login: crea un registro en login_audits
     * Previene duplicados verificando si existe un registro idéntico en los últimos 5 segundos
     */
    public static function recordLogin($user, $ipAddress = null, $userAgent = null)
    {
        $parsed = self::parseUserAgent($userAgent);
        $now = now();
        $fiveSecondsAgo = $now->copy()->subSeconds(5);

        // Verificar si existe un registro idéntico en los últimos 5 segundos
        $duplicateExists = self::where('user_id', $user->id)
            ->where('ip_address', $ipAddress)
            ->where('browser', $parsed['browser'] ?? null)
            ->where('os', $parsed['os'] ?? null)
            ->whereBetween('logged_in_at', [$fiveSecondsAgo, $now])
            ->exists();

        if ($duplicateExists) {
            return null; // No registrar duplicado
        }

        return self::create([
            'user_id' => $user->id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_name' => $parsed['device'] ?? null,
            'browser' => $parsed['browser'] ?? null,
            'os' => $parsed['os'] ?? null,
            'logged_in_at' => $now,
        ]);
    }

    /**
     * Parsear User-Agent para extraer navegador, SO y dispositivo
     */
    private static function parseUserAgent($userAgent)
    {
        $result = [
            'browser' => 'Unknown',
            'os' => 'Unknown',
            'device' => 'Unknown',
        ];

        if (!$userAgent) {
            return $result;
        }

        // Detectar navegador
        if (stripos($userAgent, 'Chrome') !== false) {
            $result['browser'] = 'Chrome';
        } elseif (stripos($userAgent, 'Firefox') !== false) {
            $result['browser'] = 'Firefox';
        } elseif (stripos($userAgent, 'Safari') !== false) {
            $result['browser'] = 'Safari';
        } elseif (stripos($userAgent, 'Edge') !== false) {
            $result['browser'] = 'Edge';
        } elseif (stripos($userAgent, 'Trident') !== false) {
            $result['browser'] = 'Internet Explorer';
        }

        // Detectar SO
        if (stripos($userAgent, 'Windows') !== false) {
            $result['os'] = 'Windows';
        } elseif (stripos($userAgent, 'Macintosh') !== false) {
            $result['os'] = 'macOS';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            $result['os'] = 'Linux';
        } elseif (stripos($userAgent, 'Android') !== false) {
            $result['os'] = 'Android';
        } elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
            $result['os'] = 'iOS';
        }

        // Detectar dispositivo
        if (stripos($userAgent, 'Mobile') !== false || stripos($userAgent, 'Android') !== false) {
            $result['device'] = 'Mobile';
        } elseif (stripos($userAgent, 'Tablet') !== false || stripos($userAgent, 'iPad') !== false) {
            $result['device'] = 'Tablet';
        } else {
            $result['device'] = 'Desktop';
        }

        return $result;
    }

    /**
     * Detectar acceso sospechoso: cambio de IP o dispositivo en un corto período
     */
    public static function detectSuspiciousActivity($user, $currentIp, $currentBrowser)
    {
        // Obtener el último acceso registrado
        $lastLogin = self::where('user_id', $user->id)
            ->orderBy('logged_in_at', 'desc')
            ->first();

        if (!$lastLogin) {
            return null; // Primer acceso
        }

        $alerts = [];

        // Alerta: IP diferente al último acceso
        if ($lastLogin->ip_address !== $currentIp && $lastLogin->ip_address !== null) {
            $alerts[] = [
                'type' => 'ip_change',
                'message' => "Acceso desde IP diferente: {$currentIp} (anterior: {$lastLogin->ip_address})",
            ];
        }

        // Alerta: navegador/dispositivo diferente al último acceso
        if ($lastLogin->browser !== $currentBrowser && $lastLogin->browser !== null) {
            $alerts[] = [
                'type' => 'browser_change',
                'message' => "Acceso desde navegador/dispositivo diferente: {$currentBrowser} (anterior: {$lastLogin->browser})",
            ];
        }

        // Alerta: si el último acceso fue hace muy poco tiempo (menos de 5 minutos)
        // esto podría indicar que alguien más está usando la cuenta
        $timeSinceLastLogin = now()->diffInMinutes($lastLogin->logged_in_at);
        if ($timeSinceLastLogin < 5 && $lastLogin->ip_address !== $currentIp) {
            $alerts[] = [
                'type' => 'simultaneous_access',
                'message' => "Posible acceso simultáneo desde múltiples dispositivos (último acceso hace {$timeSinceLastLogin} min)",
            ];
        }

        return count($alerts) > 0 ? $alerts : null;
    }

    /**
     * Obtener los últimos 10 accesos de un usuario
     */
    public static function getRecentLogins($userId, $limit = 10)
    {
        return self::where('user_id', $userId)
            ->orderBy('logged_in_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtener todos los accesos sospechosos de todos los usuarios (para el ADMINISTRADOR)
     * Detecta: cambios de IP en últimas 24 horas, cambios de dispositivo, accesos simultáneos
     * Excluye los ya aceptados por el administrador
     */
    public static function getSuspiciousLogins()
    {
        $suspiciousUsers = [];
        $usersChecked = [];

        // Obtener accesos recientes de últimas 24 horas, EXCLUIR ACEPTADOS
        $recentLogins = self::with('user')
            ->where('logged_in_at', '>=', now()->subHours(24))
            ->where(function ($query) {
                $query->where('acknowledged', '!=', true)
                    ->orWhereNull('acknowledged');
            })
            ->orderBy('logged_in_at', 'desc')
            ->get();

        foreach ($recentLogins as $login) {
            if (in_array($login->user_id, $usersChecked)) {
                continue;
            }
            $usersChecked[] = $login->user_id;

            // Obtener últimos 10 accesos del usuario
            $userLogins = self::where('user_id', $login->user_id)
                ->orderBy('logged_in_at', 'desc')
                ->limit(10)
                ->get();

            if ($userLogins->count() < 2) {
                continue;
            }

            $lastLogin = $userLogins->first();
            $alerts = [];

            // Detectar múltiples IPs diferentes en últimas 24 horas
            $uniqueIps = $userLogins->pluck('ip_address')->filter()->unique();
            if ($uniqueIps->count() > 1) {
                $ips = $uniqueIps->join(', ');
                $alerts[] = [
                    'type' => 'multiple_ips',
                    'message' => "Se detectaron accesos desde múltiples IPs en 24h: {$ips}",
                ];
            }

            // Detectar cambio de IP respecto al acceso anterior
            $secondLastLogin = $userLogins[1];
            if ($lastLogin->ip_address !== $secondLastLogin->ip_address && 
                $lastLogin->ip_address !== null && 
                $secondLastLogin->ip_address !== null) {
                $alerts[] = [
                    'type' => 'ip_change',
                    'message' => "IP diferente: {$lastLogin->ip_address} (anterior: {$secondLastLogin->ip_address})",
                ];
            }

            // Detectar cambio de navegador
            if ($lastLogin->browser !== $secondLastLogin->browser && 
                $lastLogin->browser !== null && 
                $secondLastLogin->browser !== null) {
                $alerts[] = [
                    'type' => 'browser_change',
                    'message' => "Navegador diferente: {$lastLogin->browser} (anterior: {$secondLastLogin->browser})",
                ];
            }

            // Detectar acceso simultáneo (menos de 5 minutos con IP diferente)
            $timeDiff = $lastLogin->logged_in_at->diffInMinutes($secondLastLogin->logged_in_at);
            if ($timeDiff < 5 && $lastLogin->ip_address !== $secondLastLogin->ip_address) {
                $alerts[] = [
                    'type' => 'simultaneous_access',
                    'message' => "Posible acceso simultáneo desde 2 dispositivos hace {$timeDiff} minutos",
                ];
            }

            if (count($alerts) > 0) {
                $suspiciousUsers[] = [
                    'user' => $login->user,
                    'recent_login' => $lastLogin,
                    'last_login' => $secondLastLogin,
                    'alerts' => $alerts,
                ];
            }
        }

        return $suspiciousUsers;
    }
};
