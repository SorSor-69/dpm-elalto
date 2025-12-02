<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

/**
    * The attributes that are mass assignable.
    *
    * @var list<string>
    */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol', // Agregado para roles simples
    ];
    /**
    * Verifica si el usuario tiene un rol específico
    */
    public function esRol($rol)
    {
        return $this->rol === $rol;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function funcionario()
    {
        return $this->hasOne(Funcionario::class);
    }
    public function adminlte_image()
    {
        // If the related funcionario has a profile photo, use it
        try {
            if ($this->funcionario && !empty($this->funcionario->foto_perfil)) {
                return asset('storage/' . $this->funcionario->foto_perfil);
            }
        } catch (\Throwable $e) {
            // ignore and fallback
        }

        $genero = null;
        try {
            $genero = $this->funcionario->genero ?? $this->genero ?? null;
        } catch (\Throwable $e) {
            $genero = $this->genero ?? null;
        }

        if ($genero === 0) {
            return asset('img/gamea/perfil-mujer.png'); // imagen por género mujer
        } elseif ($genero === 1) {
            return asset('img/gamea/perfil-varon.png'); // imagen por género varón
        }

        // Imagen por defecto si no se especifica género
        return 'https://picsum.photos/300/300';
    }

    public function adminlte_desc(){
        return $this->rol ?? 'Sin rol';
    }

     public function adminlte_profile_url()
    {
        return route('profile.show'); // o la ruta que uses para el perfil
    }
}
