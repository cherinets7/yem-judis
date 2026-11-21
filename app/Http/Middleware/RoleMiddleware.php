<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Support\PermissionCatalog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Allow an active super admin, or an active user whose role is listed.
     *
     * Usage: role:zone_head,legal_expert
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->is_active) {
            abort(403, 'ይህን ገጽ ለመጠቀም ንቁ መለያ ያስፈልጋል።');
        }

        $role = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;
        $allowed = [];

        foreach ($roles as $token) {
            if (PermissionCatalog::isGroup($token)) {
                $allowed = array_merge($allowed, PermissionCatalog::roles($token));
            } else {
                $allowed[] = $token;
            }
        }

        if ($role === UserRole::SuperAdmin->value || in_array($role, $allowed, true)) {
            return $next($request);
        }

        abort(403, 'ይህን ገጽ የመጠቀም ፈቃድ የለዎትም።');
    }
}
