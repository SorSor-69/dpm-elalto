<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$funcId = $argv[1] ?? null;
if (!$funcId) {
    echo "Usage: php list_asignaciones_func.php <funcionario_id>\n";
    exit(1);
}
$rows = \App\Models\FuncionarioInspeccion::where('funcionario_id', $funcId)
    ->select('inspeccion_id','rol_en_inspeccion')
    ->get();
if ($rows->isEmpty()) {
    echo "No assignments found for funcionario_id={$funcId}\n";
    exit(0);
}
foreach ($rows as $r) {
    echo (string)$r->inspeccion_id . ' | ' . ($r->rol_en_inspeccion ?? 'NULL') . PHP_EOL;
}
