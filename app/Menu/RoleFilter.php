<?php

namespace App\Menu;

use JeroenNoten\LaravelAdminLte\Helpers\SidebarItemHelper;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Handle the menu item and remove it if current user role not allowed.
     */
    public function transform($item, $helper = null)
    {
        // If 'roles' is not set, allow by default
        if (empty($item['roles'])) {
            return $item;
        }

        $user = auth()->user();
        if (!$user) return false;

        // Eloquent models may not have a declared property 'rol', so access dynamically
        $role = null;
        try {
            $role = $user->rol ?? null;
        } catch (\Throwable $e) {
            $role = null;
        }
        if (!$role && method_exists($user, 'getRoleNames')) {
            $role = $user->getRoleNames()->first() ?? null;
        }

        // If role in allowed list, keep the item; otherwise remove
        if (is_array($item['roles']) && $role && in_array($role, $item['roles'])) {
            return $item;
        }

        return false;
    }
}
