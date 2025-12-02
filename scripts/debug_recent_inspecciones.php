<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Inspeccion;
use App\Models\FuncionarioInspeccion;

$inspecciones = Inspeccion::with('proyecto')->orderByDesc('fecha')->take(15)->get();
if ($inspecciones->isEmpty()) {
    echo "No inspecciones found\n";
    exit;
}
foreach ($inspecciones as $ins) {
    $id = (string)($ins->_id ?? $ins->id);
    $fecha = $ins->fecha ?? $ins->created_at ?? 'N/A';
    $created_by = $ins->created_by ?? 'N/A';
    $count = FuncionarioInspeccion::where('inspeccion_id', (string)$id)->count();
    echo "$id | fecha=" . (is_object($fecha) ? $fecha : $fecha) . " | created_by={$created_by} | asignaciones={$count}\n";
}
