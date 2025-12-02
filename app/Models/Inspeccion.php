<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Inspeccion extends Model
{
    use HasFactory;

    protected $table = 'inspecciones';

    protected $fillable = [
        'proyecto_id',
        'actividad',
        'tiempo_inspeccion',
        'observaciones',
        'activo',
        'fecha', // Campo de fecha para desempeño
        'asignado_por',
        'created_by',
        // Campos de tiempos y ubicación
        'hora_salida_gamea',
        'hora_llegada_obra',
        'hora_salida_obra',
        'hora_llegada_gamea',
        'duracion',
        'latitud',
        'longitud',
        'foto_llegada_obra',
        // Si se usa proyecto_manual
        'proyecto_manual',
        // Otros posibles campos
        'detalle_inspeccion',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, );
    }

    public function funcionarios()
    {
        return $this->hasMany(FuncionarioInspeccion::class, 'inspeccion_id');
    }
}
