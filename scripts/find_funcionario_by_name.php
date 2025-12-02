<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$term = 'Ruben';
$rows = \App\Models\Funcionario::where('nombres', 'LIKE', "%{$term}%")->orWhere('apellidos', 'LIKE', "%{$term}%")->get();
if ($rows->isEmpty()) {
    echo "No funcionario found matching '{$term}'\n";
    exit(0);
}
foreach ($rows as $r) {
    echo "id=" . (string)$r->id . " | user_id=" . (string)($r->user_id ?? '') . " | name=" . ($r->nombres ?? '') . " " . ($r->apellidos ?? '') . "\n";
}
