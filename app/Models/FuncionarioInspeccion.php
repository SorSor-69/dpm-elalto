<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class FuncionarioInspeccion extends Model
{
    use HasFactory;

    protected $table = 'funcionario_inspeccion';

    protected $casts = [
        'detalle_inspeccion' => 'array',
    ];

    // Asegura que los campos de ubicación de detalle existan
    // Si no existen en la migración, debes agregarlos como tipo nullable

    protected $fillable = [
        'funcionario_id',
        'inspeccion_id',
        'rol_en_inspeccion',
        // Ubicaciones de cada acción
        'latitud_salida_gamea',
        'longitud_salida_gamea',
        'latitud_llegada_obra',
        'longitud_llegada_obra',
        'latitud_foto_llegada_obra',
        'longitud_foto_llegada_obra',
        'latitud_salida_obra',
        'longitud_salida_obra',
        'latitud_llegada_gamea',
        'longitud_llegada_gamea',
        // Tiempos y otros datos
        'hora_salida_gamea',
        'hora_llegada_obra',
        'hora_salida_obra',
        'hora_llegada_gamea',
        'foto_llegada_obra',
        'detalle_inspeccion',
        'observaciones',
        'notificado',
        'activo',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function inspeccion()
    {
        return $this->belongsTo(Inspeccion::class, 'inspeccion_id', '_id');
    }
}