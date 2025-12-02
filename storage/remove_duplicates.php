<?php
// Script para eliminar duplicados en login_audits
require __DIR__ . '/../bootstrap/app.php';

use App\Models\LoginAudit;

$duplicates = LoginAudit::selectRaw('user_id, ip_address, browser, logged_in_at, COUNT(*) as count')
    ->groupBy('user_id', 'ip_address', 'browser', 'logged_in_at')
    ->having('count', '>', 1)
    ->get();

echo "Duplicados encontrados: " . count($duplicates) . "\n";

foreach ($duplicates as $dup) {
    $toDelete = LoginAudit::where('user_id', $dup->user_id)
        ->where('ip_address', $dup->ip_address)
        ->where('browser', $dup->browser)
        ->where('logged_in_at', $dup->logged_in_at)
        ->skip(1)  // Mantener el primero
        ->get();
    
    foreach ($toDelete as $record) {
        echo "Eliminando duplicado: User {$record->user_id}, IP {$record->ip_address}\n";
        $record->delete();
    }
}

echo "Proceso completado.\n";
