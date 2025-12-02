<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\LoginAudit;

class RecordLoginListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $request = request();
        $user = $event->user;
        $ipAddress = $request->ip();
        $userAgent = $request->header('User-Agent');

        LoginAudit::recordLogin($user, $ipAddress, $userAgent);
    }
}
