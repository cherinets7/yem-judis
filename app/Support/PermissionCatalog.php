<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\RolePermission;

class PermissionCatalog
{
    /**
     * @return array<string, array{label: string, defaults: list<string>}>
     */
    public static function groups(): array
    {
        return [
            'all' => [
                'label' => 'ዳሽቦርድ እና ወረዳዎች',
                'defaults' => Roles::list(Roles::ALL),
            ],
            'zone' => [
                'label' => 'ዞን ማዕከል',
                'defaults' => Roles::list(Roles::ZONE),
            ],
            'reports' => [
                'label' => 'ዓመታዊ፣ ሳምንታዊ እና ስትራቴጂክ',
                'defaults' => Roles::list(Roles::REPORTS),
            ],
            'reviewers' => [
                'label' => 'ማጽደቅ እና ውድቅ ማድረግ',
                'defaults' => Roles::list(Roles::REVIEWERS),
            ],
            'hr' => [
                'label' => 'የሰው ሀብት',
                'defaults' => Roles::list(Roles::HR),
            ],
            'legal' => [
                'label' => 'መዝገቦች 001–014',
                'defaults' => Roles::list(Roles::LEGAL),
            ],
            'laws' => [
                'label' => 'ህጎች',
                'defaults' => Roles::list(Roles::LAWS),
            ],
            'community' => [
                'label' => 'ማህበረሰብ እና አስተያየት',
                'defaults' => Roles::list(Roles::COMMUNITY),
            ],
            'tracking' => [
                'label' => 'የፐብሊክ ጉዳይ መከታተያ',
                'defaults' => Roles::list(Roles::TRACKING),
            ],
            'settings' => [
                'label' => 'ቅንብር ማስተካከል',
                'defaults' => [UserRole::ZoneHead->value],
            ],
            'permissions' => [
                'label' => 'ፈቃድ ማስተካከል',
                'defaults' => [],
            ],
            'structure' => [
                'label' => 'ዞን እና ወረዳ መመዝገብ',
                'defaults' => [UserRole::ZoneHead->value],
            ],
            'experts' => [
                'label' => 'ባለሙያ መመዝገብ',
                'defaults' => [UserRole::ZoneHead->value, UserRole::HrExpert->value, UserRole::OfficeHead->value],
            ],
            'performance' => [
                'label' => 'አፈጻጸም ለኃላፊ ማቅረብ',
                'defaults' => Roles::list(Roles::ALL),
            ],
        ];
    }

    public static function isGroup(string $name): bool
    {
        return array_key_exists($name, self::groups());
    }

    /**
     * @return list<string>
     */
    public static function roles(string $group): array
    {
        $definition = self::groups()[$group] ?? null;

        if ($definition === null) {
            return [];
        }

        $stored = RolePermission::query()->where('group', $group)->get();

        if ($stored->isEmpty()) {
            return $definition['defaults'];
        }

        return $stored
            ->filter(fn (RolePermission $permission): bool => $permission->allowed)
            ->pluck('role')
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $roles
     */
    public static function sync(string $group, array $roles): void
    {
        if (! self::isGroup($group)) {
            return;
        }

        $allowed = array_fill_keys($roles, true);

        foreach (self::assignableRoles() as $role) {
            RolePermission::query()->updateOrCreate(
                ['group' => $group, 'role' => $role],
                ['allowed' => isset($allowed[$role])],
            );
        }
    }

    public static function seedDefaults(): void
    {
        foreach (self::groups() as $group => $definition) {
            self::sync($group, $definition['defaults']);
        }
    }

    /**
     * @return list<string>
     */
    public static function assignableRoles(): array
    {
        return array_values(array_filter(
            UserRole::values(),
            fn (string $role): bool => $role !== UserRole::SuperAdmin->value,
        ));
    }
}
