<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Inspeccion;
use App\Models\Funcionario;
use App\Models\FuncionarioInspeccion;
use Carbon\Carbon;

echo "=== Diagnóstico de Inspecciones ===\n";

// Total inspections
try {
    $total = Inspeccion::count();
    echo "Total Inspecciones en colección: " . $total . "\n";
} catch (\Exception $e) {
    echo "Error contando Inspeccion: " . $e->getMessage() . "\n";
}

// Show a sample inspeccion
$sample = Inspeccion::first();
if ($sample) {
    echo "Sample Inspeccion ID: " . ($sample->_id ?? $sample->id ?? 'n/a') . "\n";
    echo "Fields (fecha / created_at):\n";
    echo " fecha: ";
    var_export($sample->fecha ?? null);
    echo "\n created_at: ";
    var_export($sample->created_at ?? null);
    echo "\n";
} else {
    echo "No se encontró ninguna Inspeccion.\n";
}

// Periodos: día, semana, mes, año -> show counts per funcionario
$periods = [
    'dia' => function() { $d = Carbon::now(); return [ $d->copy()->startOfDay(), $d->copy()->endOfDay() ]; },
    'semana' => function() { $d = Carbon::now(); return [ $d->copy()->startOfWeek(), $d->copy()->endOfWeek() ]; },
    'mes' => function() { $d = Carbon::now(); return [ $d->copy()->startOfMonth(), $d->copy()->endOfMonth() ]; },
    'año' => function() { $d = Carbon::now(); return [ $d->copy()->startOfYear(), $d->copy()->endOfYear() ]; },
];

foreach ($periods as $key => $fn) {
    list($start, $end) = $fn();
    echo "\nPeriodo: $key -> $start to $end\n";
    try {
        $count = FuncionarioInspeccion::whereHas('inspeccion', function($q) use ($start, $end) {
            $q->where(function($qq) use ($start, $end) {
                $qq->whereBetween('fecha', [$start, $end])->orWhereBetween('created_at', [$start, $end]);
            });
        })->count();
        echo "Total asignaciones con inspeccion en periodo: " . $count . "\n";
    } catch (\Exception $e) {
        echo "Error en query periodo $key: " . $e->getMessage() . "\n";
    }

    // Per funcionario sample (limit 10)
    echo "Conteo por funcionario (primeros 10):\n";
    $funcionarios = Funcionario::where('activo', true)->take(10)->get();
    foreach ($funcionarios as $f) {
        try {
            $c = FuncionarioInspeccion::where('funcionario_id', $f->id)
                ->whereHas('inspeccion', function($q) use ($start, $end) {
                    $q->where(function($qq) use ($start, $end) {
                        $qq->whereBetween('fecha', [$start, $end])->orWhereBetween('created_at', [$start, $end]);
                    });
                })->count();
            echo " - {$f->nombres} {$f->apellidos} ({$f->id}): {$c}\n";
        } catch (\Exception $e) {
            echo " - Error al contar para {$f->id}: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nDiagnóstico finalizado.\n";