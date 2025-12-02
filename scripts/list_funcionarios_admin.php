<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = \App\Models\Funcionario::where('rol', 'ADMINISTRADOR')->get();
if ($rows->isEmpty()) {
    echo "No funcionarios with rol ADMINISTRADOR found\n";
    exit(0);
}
foreach ($rows as $r) {
    echo "id=" . (string)$r->id . " | user_id=" . (string)($r->user_id ?? '') . " | name=" . ($r->nombres ?? '') . " " . ($r->apellidos ?? '') . "\n";
}
