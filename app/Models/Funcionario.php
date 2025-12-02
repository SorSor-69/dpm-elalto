<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Funcionario extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'nombres',
        'apellidos',
        'ci',
        'complemento',
        'expedido',
        'correo',
        'celular',
        'genero',
        'cargo',
        'activo',
        'password',
        'fecha_nacimiento',
        'fecha_registro',
        'rol', // Agregado para roles simples
    ];
    /**
     * Verifica si el funcionario tiene un rol específico
     */
    public function esRol($rol)
    {
        return $this->rol === $rol;
    }

    protected $hidden = [
        'password',
    ];

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inspecciones()
    {
        return $this->belongsToMany(Inspeccion::class, 'funcionario_inspeccion')
            ->withPivot('latitud', 'longitud', 'hora_salida', 'hora_llegada');
    }
}
