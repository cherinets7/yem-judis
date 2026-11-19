<?php

namespace App\Support;

use App\Enums\OrganizationType;
use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\User;

class OrganizationScope
{
    /**
     * Organization ids the user may read and write.
     *
     * Office head and office plan expert stay on their own organization.
     * Zone head and zone plan expert see the zone and every child office.
     * Other zone-office roles see that same family. Everyone else sees only
     * their own organization. Super admin sees every organization.
     *
     * @return list<int>
     */
    public static function ids(?User $user = null): array
    {
        $user ??= auth()->user();
        $role = Roles::of($user);

        if ($user === null || $role === null) {
            return [];
        }

        if ($role === UserRole::SuperAdmin) {
            return Organization::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        $orgId = (int) $user->organization_id;

        if (in_array($role, [UserRole::OfficeHead, UserRole::OfficePlanExpert], true)) {
            return [$orgId];
        }

        if (in_array($role, [UserRole::ZoneHead, UserRole::ZonePlanExpert], true) || self::belongsToZone($user)) {
            return self::familyIds(self::zoneId($user));
        }

        return [$orgId];
    }

    public static function includesFamily(?User $user = null): bool
    {
        $user ??= auth()->user();
        $role = Roles::of($user);

        if ($user === null || $role === null) {
            return false;
        }

        if ($role === UserRole::SuperAdmin) {
            return true;
        }

        if (in_array($role, [UserRole::OfficeHead, UserRole::OfficePlanExpert], true)) {
            return false;
        }

        return in_array($role, [UserRole::ZoneHead, UserRole::ZonePlanExpert], true) || self::belongsToZone($user);
    }

    private static function belongsToZone(User $user): bool
    {
        $user->loadMissing('organization');

        return $user->organization?->type === OrganizationType::Zone;
    }

    private static function zoneId(User $user): int
    {
        $user->loadMissing('organization');
        $organization = $user->organization;

        if ($organization === null) {
            return (int) $user->organization_id;
        }

        if ($organization->type === OrganizationType::Zone) {
            return (int) $organization->id;
        }

        return (int) ($organization->parent_id ?: $organization->id);
    }

    /**
     * @return list<int>
     */
    private static function familyIds(int $zoneId): array
    {
        return Organization::query()
            ->where('id', $zoneId)
            ->orWhere('parent_id', $zoneId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
