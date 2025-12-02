<?php
// Script temporal para listar asignaciones de un funcionario
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$funcId = '688ac8911324542a6901c206'; // reemplaza si hace falta
$rows = \App\Models\FuncionarioInspeccion::where('funcionario_id', $funcId)
    ->select('inspeccion_id', 'rol_en_inspeccion')
    ->get();

if ($rows->isEmpty()) {
    echo "No assignments found for funcionario_id={$funcId}\n";
    exit(0);
}

foreach ($rows as $r) {
    echo (string)$r->inspeccion_id . ' | ' . ($r->rol_en_inspeccion ?? 'NULL') . "\n";
}
