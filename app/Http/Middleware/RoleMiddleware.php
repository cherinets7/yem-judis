<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
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

        if ($role === UserRole::SuperAdmin->value || in_array($role, $roles, true)) {
            return $next($request);
        }

        abort(403, 'ይህን ገጽ የመጠቀም ፈቃድ የለዎትም።');
    }
}
