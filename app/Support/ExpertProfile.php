<?php

namespace App\Support;

use App\Enums\StaffCategory;
use App\Enums\UserRole;
use App\Models\StaffProfile;
use App\Models\User;

class ExpertProfile
{
    public static function sync(User $expert, ?User $actor = null): StaffProfile
    {
        $profile = StaffProfile::query()->firstWhere('user_id', $expert->id);
        $values = [
            'organization_id' => $expert->organization_id,
            'work_unit_id' => $expert->work_unit_id,
            'staff_category' => self::category($expert),
            'full_name' => $expert->displayName(),
            'job_title' => Roles::of($expert)?->label(),
        ];

        if ($profile === null) {
            return StaffProfile::query()->create($values + [
                'user_id' => $expert->id,
                'created_by' => $actor?->id,
                'filled_by_expert' => $actor?->id,
            ]);
        }

        $profile->update($values);

        return $profile;
    }

    public static function category(User $expert): StaffCategory
    {
        return match (Roles::of($expert)) {
            UserRole::LegalExpert, UserRole::OfficeHead, UserRole::UnitCoordinator => StaffCategory::Prosecutor,
            default => StaffCategory::PublicServant,
        };
    }
}
