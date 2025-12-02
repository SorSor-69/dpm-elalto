<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Proyecto extends Model
{
    use HasFactory;

    protected $table = 'proyectos';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha_creacion',
        'hora_creacion',
        'latitud',
        'longitud',
        'distrito',
        'presupuesto',
        'estado',
        'activo',
        'fecha_conclusion', 
    ];

    public $timestamps = true;

    protected $casts = [
        'fecha_creacion' => 'date',
        'hora_creacion' => 'datetime:H:i:s',
    ];

    public function inspecciones()
    {
        return $this->hasMany(Inspeccion::class);
    }
}
