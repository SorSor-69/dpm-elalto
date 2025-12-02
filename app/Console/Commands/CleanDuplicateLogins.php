<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LoginAudit;
use Illuminate\Support\Facades\DB;

class CleanDuplicateLogins extends Command
{
    protected $signature = 'logins:clean-duplicates';
    protected $description = 'Elimina registros de login duplicados idénticos';

    public function handle()
    {
        $this->info('Limpiando registros de login duplicados...');

        // Obtener todos los logins agrupados por usuario, IP, navegador y SO
        $duplicates = LoginAudit::select('user_id', 'ip_address', 'browser', 'os')
            ->groupBy('user_id', 'ip_address', 'browser', 'os')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $deletedCount = 0;

        foreach ($duplicates as $group) {
            // Obtener todos los registros de este grupo
            $logins = LoginAudit::where('user_id', $group->user_id)
                ->where('ip_address', $group->ip_address)
                ->where('browser', $group->browser)
                ->where('os', $group->os)
                ->orderBy('logged_in_at', 'desc')
                ->get();

            // Mantener solo el más reciente, eliminar los demás
            if ($logins->count() > 1) {
                $recent = $logins->first();
                $duplicatesToDelete = $logins->slice(1);

                foreach ($duplicatesToDelete as $duplicate) {
                    $duplicate->delete();
                    $deletedCount++;
                }

                $this->line("Usuario {$group->user_id} - IP {$group->ip_address}: {$duplicatesToDelete->count()} registros eliminados");
            }
        }

        $this->info("Total de registros duplicados eliminados: {$deletedCount}");
    }
}
