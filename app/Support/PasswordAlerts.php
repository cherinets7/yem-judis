<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\PasswordResetRequested;

class PasswordAlerts
{
    public static function notify(User $account): void
    {
        if (Roles::of($account) === UserRole::SuperAdmin) {
            return;
        }

        $account->loadMissing('organization');
        $organization = $account->organization;

        if ($organization === null) {
            return;
        }

        $ids = array_values(array_unique(array_filter([
            (int) $organization->id,
            $organization->parent_id ? (int) $organization->parent_id : null,
        ])));

        User::query()
            ->whereIn('organization_id', $ids)
            ->whereIn('role', [UserRole::ZoneHead->value, UserRole::OfficeHead->value, UserRole::HrExpert->value])
            ->where('is_active', true)
            ->whereKeyNot($account->id)
            ->get()
            ->each(fn (User $helper) => $helper->notify(new PasswordResetRequested($account)));
    }
}
