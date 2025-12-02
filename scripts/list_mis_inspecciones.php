<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$funcId = $argv[1] ?? null;
if (!$funcId) {
    echo "Usage: php list_mis_inspecciones.php <funcionario_id>\n";
    exit(1);
}
$inspeccionIds = \App\Models\FuncionarioInspeccion::where('funcionario_id', $funcId)
    ->whereIn('rol_en_inspeccion', ['ADMINISTRADOR','JEFE'])
    ->pluck('inspeccion_id')
    ->map(function($id){ return (string)$id; })
    ->unique()
    ->values()
    ->toArray();

if (empty($inspeccionIds)) {
    echo "No inspeccionIds found for funcionario {$funcId}\n";
    exit(0);
}

$inspecciones = \App\Models\Inspeccion::with('proyecto','funcionarios.funcionario')->whereIn('_id', $inspeccionIds)->get();
if ($inspecciones->isEmpty()) {
    echo "No inspecciones fetched for ids: " . implode(',', $inspeccionIds) . "\n";
    exit(0);
}
foreach ($inspecciones as $ins) {
    echo (string)($ins->_id ?? $ins->id) . ' | ' . ($ins->proyecto->nombre ?? $ins->proyecto_manual ?? 'N/A') . "\n";
    foreach ($ins->funcionarios as $f) {
        echo "  - asign: funcionario_id=".($f->funcionario_id ?? '') . " rol=".($f->rol_en_inspeccion ?? '') . "\n";
    }
}
