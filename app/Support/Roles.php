<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;

class Roles
{
    public const ALL = 'zone_head,zone_plan_expert,unit_coordinator,hr_expert,legal_expert,office_head,office_plan_expert';

    public const ZONE = 'zone_head,zone_plan_expert,unit_coordinator,hr_expert,legal_expert';

    public const REPORTS = 'zone_head,zone_plan_expert,office_head,office_plan_expert,unit_coordinator';

    public const REVIEWERS = 'zone_head,office_head';

    public const HR = 'hr_expert,zone_head,office_head';

    public const LEGAL = 'legal_expert,zone_head,office_head';

    public const LAWS = 'legal_expert,zone_head';

    public const COMMUNITY = 'legal_expert,zone_head,office_head';

    public const TRACKING = 'legal_expert,zone_head,office_head,office_plan_expert';

    /**
     * @return list<string>
     */
    public static function list(string $roles): array
    {
        return $roles === '' ? [] : explode(',', $roles);
    }

    public static function allows(?User $user, string $roles): bool
    {
        $role = self::of($user);

        if ($user === null || ! $user->is_active || $role === null) {
            return false;
        }

        if ($role === UserRole::SuperAdmin) {
            return true;
        }

        return in_array($role->value, self::list($roles), true);
    }

    public static function of(?User $user): ?UserRole
    {
        if ($user === null) {
            return null;
        }

        $role = $user->role;

        if ($role instanceof UserRole) {
            return $role;
        }

        return UserRole::tryFrom((string) $role);
    }
}
