<?php

namespace App\Support;

use App\Enums\OrganizationType;
use App\Enums\ReviewStatus;
use App\Enums\StaffCategory;
use App\Enums\UserRole;
use App\Models\AnnualPlan;
use App\Models\CivilCase;
use App\Models\CommunityPost;
use App\Models\CriminalCase;
use App\Models\Law;
use App\Models\Organization;
use App\Models\PerformanceReport;
use App\Models\StaffProfile;
use App\Models\StrategicFocusData;
use App\Models\User;
use App\Models\WeeklyReport;
use Illuminate\Support\Facades\Route;

class RoleDashboard
{
    /**
     * @param  list<int>  $ids
     * @return array{
     *     title: string,
     *     subtitle: string,
     *     role_label: string,
     *     kpis: list<array{label: string, value: int|string, meta: string}>,
     *     shortcuts: list<array{label: string, url: string}>,
     *     show_waiting: bool,
     *     show_matrix: bool,
     *     show_mine: bool,
     *     mine_label: string
     * }
     */
    public static function for(?User $user, array $ids): array
    {
        $role = Roles::of($user);
        $label = $role?->label() ?? 'ሰራተኛ';

        return match ($role) {
            UserRole::SuperAdmin => self::superAdmin($label, $ids),
            UserRole::ZoneHead => self::zoneHead($label, $ids),
            UserRole::OfficeHead => self::officeHead($label, $ids, $user),
            UserRole::ZonePlanExpert, UserRole::OfficePlanExpert => self::planner($label, $ids, $user),
            UserRole::LegalExpert => self::legal($label, $ids),
            UserRole::HrExpert => self::hr($label, $ids),
            UserRole::UnitCoordinator => self::coordinator($label, $ids, $user),
            default => self::fallback($label, $ids, $user),
        };
    }

    /**
     * @param  list<int>  $ids
     * @return array<string, mixed>
     */
    private static function superAdmin(string $label, array $ids): array
    {
        return [
            'title' => 'የሲስተም ዳሽቦርድ',
            'subtitle' => 'ሁሉንም ተቋማት፣ ፈቃዶች እና ግምገማዎች በአንድ እይታ።',
            'role_label' => $label,
            'kpis' => [
                ['label' => 'ተቋማት', 'value' => Organization::query()->count(), 'meta' => 'ዞን እና ወረዳዎች'],
                ['label' => 'ተጠቃሚዎች', 'value' => User::query()->where('is_active', true)->count(), 'meta' => 'ንቁ መለያዎች'],
                ['label' => 'ለማጽደቅ', 'value' => self::waitingTotal($ids), 'meta' => 'ሁሉም ሪፖርቶች'],
                ['label' => 'ህጎች', 'value' => Law::query()->count(), 'meta' => 'የተመዘገቡ'],
            ],
            'shortcuts' => array_values(array_filter([
                self::link('ቢሮዎች', 'admin.offices.index'),
                self::link('ባለሙያዎች', 'admin.experts.index'),
                self::link('ፈቃዶች', 'permissions.edit'),
                self::link('ቅንብሮች', 'settings.edit'),
                self::link('ኦዲት', 'admin.audit.index'),
            ])),
            'show_waiting' => true,
            'show_matrix' => true,
            'show_mine' => false,
            'mine_label' => '',
        ];
    }

    /**
     * @param  list<int>  $ids
     * @return array<string, mixed>
     */
    private static function zoneHead(string $label, array $ids): array
    {
        return [
            'title' => 'የዞን ኃላፊ ዳሽቦርድ',
            'subtitle' => 'የዞኑ ሪፖርቶች፣ ወረዳዎች እና የማጽደቅ ወረፋ።',
            'role_label' => $label,
            'kpis' => [
                ['label' => 'ወረዳ/ከተማ', 'value' => Organization::query()->whereIn('id', $ids)->whereIn('type', [OrganizationType::Woreda, OrganizationType::City])->count(), 'meta' => 'በእይታዎ'],
                ['label' => 'ለማጽደቅ', 'value' => self::waitingTotal($ids), 'meta' => 'ቀርቧል'],
                ['label' => 'ባለሙያዎች', 'value' => User::query()->whereIn('organization_id', $ids)->where('is_active', true)->count(), 'meta' => 'ንቁ'],
                ['label' => 'ሰራተኛ መገለጫ', 'value' => StaffProfile::query()->whereIn('organization_id', $ids)->count(), 'meta' => 'HR'],
            ],
            'shortcuts' => array_values(array_filter([
                self::link('ዞን ማዕከል', 'zone-center'),
                self::link('ወረዳዎች', 'woredas'),
                self::link('ቢሮዎች', 'admin.offices.index'),
                self::link('ባለሙያዎች', 'admin.experts.index'),
                self::link('አፈጻጸም', 'admin.performance.index'),
            ])),
            'show_waiting' => true,
            'show_matrix' => true,
            'show_mine' => false,
            'mine_label' => '',
        ];
    }

    /**
     * @param  list<int>  $ids
     * @return array<string, mixed>
     */
    private static function officeHead(string $label, array $ids, ?User $user): array
    {
        return [
            'title' => 'የጽህፈት ቤት ኃላፊ ዳሽቦርድ',
            'subtitle' => 'የቢሮዎ ሪፖርቶች ማጽደቅ እና የሰራተኛ አፈጻጸም።',
            'role_label' => $label,
            'kpis' => [
                ['label' => 'ለማጽደቅ', 'value' => self::waitingTotal($ids), 'meta' => 'የቢሮዎ'],
                ['label' => 'ባለሙያዎች', 'value' => User::query()->whereIn('organization_id', $ids)->where('is_active', true)->count(), 'meta' => 'በቢሮዎ'],
                ['label' => 'የእኔ አፈጻጸም', 'value' => PerformanceReport::query()->where('user_id', $user?->id)->count(), 'meta' => 'ሁሉም ጊዜ'],
                ['label' => 'ሳምንታዊ', 'value' => WeeklyReport::query()->whereIn('organization_id', $ids)->where('status', ReviewStatus::Submitted)->count(), 'meta' => 'ቀርቧል'],
            ],
            'shortcuts' => array_values(array_filter([
                self::link('አፈጻጸም', 'admin.performance.index'),
                self::link('ባለሙያዎች', 'admin.experts.index'),
                self::link('ሳምንታዊ ሪፖርት', 'admin.weekly-reports.index'),
                self::link('ዓመታዊ እቅድ', 'admin.annual-plans.index'),
            ])),
            'show_waiting' => true,
            'show_matrix' => true,
            'show_mine' => true,
            'mine_label' => 'የእኔ አፈጻጸም',
        ];
    }

    /**
     * @param  list<int>  $ids
     * @return array<string, mixed>
     */
    private static function planner(string $label, array $ids, ?User $user): array
    {
        $mine = fn (string $model) => $model::query()->where('user_id', $user?->id);

        return [
            'title' => 'የዕቅድ ባለሙያ ዳሽቦርድ',
            'subtitle' => 'ዓመታዊ፣ ሳምንታዊ እና ስትራቴጂክ ሪፖርቶችዎ።',
            'role_label' => $label,
            'kpis' => [
                ['label' => 'ዓመታዊ', 'value' => $mine(AnnualPlan::class)->count(), 'meta' => 'የእኔ'],
                ['label' => 'ሳምንታዊ', 'value' => $mine(WeeklyReport::class)->count(), 'meta' => 'የእኔ'],
                ['label' => 'ስትራቴጂክ', 'value' => $mine(StrategicFocusData::class)->count(), 'meta' => 'የእኔ'],
                ['label' => 'ረቂቅ', 'value' => $mine(AnnualPlan::class)->where('status', ReviewStatus::Draft)->count()
                    + $mine(WeeklyReport::class)->where('status', ReviewStatus::Draft)->count(), 'meta' => 'ያልቀረቡ'],
            ],
            'shortcuts' => array_values(array_filter([
                self::link('ዓመታዊ እቅድ', 'admin.annual-plans.index'),
                self::link('ሳምንታዊ ሪፖርት', 'admin.weekly-reports.index'),
                self::link('ስትራቴጂክ', 'admin.strategic-focus.index'),
                self::link('አፈጻጸም', 'admin.performance.index'),
            ])),
            'show_waiting' => false,
            'show_matrix' => true,
            'show_mine' => true,
            'mine_label' => 'የእኔ አፈጻጸም',
        ];
    }

    /**
     * @param  list<int>  $ids
     * @return array<string, mixed>
     */
    private static function legal(string $label, array $ids): array
    {
        return [
            'title' => 'የህግ ባለሙያ ዳሽቦርድ',
            'subtitle' => 'መዝገቦች፣ ህጎች፣ ማህበረሰብ እና ጉዳይ ክትትል።',
            'role_label' => $label,
            'kpis' => [
                ['label' => 'ወንጀል', 'value' => CriminalCase::query()->whereIn('organization_id', $ids)->count(), 'meta' => 'መዝገቦች'],
                ['label' => 'ፍትሐ ብሔር', 'value' => CivilCase::query()->whereIn('organization_id', $ids)->count(), 'meta' => 'መዝገቦች'],
                ['label' => 'ያልታተሙ ህጎች', 'value' => Law::query()->where('is_published', false)->count(), 'meta' => 'ለህዝብ'],
                ['label' => 'አስተያየት', 'value' => CommunityPost::query()->where('is_published', false)->count(), 'meta' => 'ለግምገማ'],
            ],
            'shortcuts' => array_values(array_filter([
                self::link('ወንጀል ጉዳይ', 'admin.criminal-cases.index'),
                self::link('ፍትሐ ብሔር', 'admin.civil-cases.index'),
                self::link('ህጎች', 'admin.laws.index'),
                self::link('ማህበረሰብ', 'admin.community-posts.index'),
                self::link('ፍለጋ', 'admin.search'),
            ])),
            'show_waiting' => false,
            'show_matrix' => false,
            'show_mine' => true,
            'mine_label' => 'የእኔ አፈጻጸም',
        ];
    }

    /**
     * @param  list<int>  $ids
     * @return array<string, mixed>
     */
    private static function hr(string $label, array $ids): array
    {
        return [
            'title' => 'የሰው ሀብት ዳሽቦርድ',
            'subtitle' => 'የሰራተኛ መገለጫዎች እና ባለሙያ አስተዳደር።',
            'role_label' => $label,
            'kpis' => [
                ['label' => 'መገለጫዎች', 'value' => StaffProfile::query()->whereIn('organization_id', $ids)->count(), 'meta' => 'HR'],
                ['label' => 'ባለሙያዎች', 'value' => User::query()->whereIn('organization_id', $ids)->where('is_active', true)->count(), 'meta' => 'ንቁ'],
                ['label' => 'ዐቃቤ ህግ', 'value' => StaffProfile::query()->whereIn('organization_id', $ids)->where('staff_category', StaffCategory::Prosecutor)->count(), 'meta' => 'ምድብ'],
                ['label' => 'ፐብሊክ ሰርቫንት', 'value' => StaffProfile::query()->whereIn('organization_id', $ids)->where('staff_category', StaffCategory::PublicServant)->count(), 'meta' => 'ምድብ'],
            ],
            'shortcuts' => array_values(array_filter([
                self::link('የሰው ሀብት', 'admin.staff-profiles.index'),
                self::link('ባለሙያዎች', 'admin.experts.index'),
                self::link('አፈጻጸም', 'admin.performance.index'),
            ])),
            'show_waiting' => false,
            'show_matrix' => false,
            'show_mine' => true,
            'mine_label' => 'የእኔ አፈጻጸም',
        ];
    }

    /**
     * @param  list<int>  $ids
     * @return array<string, mixed>
     */
    private static function coordinator(string $label, array $ids, ?User $user): array
    {
        return [
            'title' => 'የክፍል አስተባባሪ ዳሽቦርድ',
            'subtitle' => 'የክፍልዎ ሪፖርቶች እና የግል አፈጻጸም።',
            'role_label' => $label,
            'kpis' => [
                ['label' => 'ሳምንታዊ', 'value' => WeeklyReport::query()->where('user_id', $user?->id)->count(), 'meta' => 'የእኔ'],
                ['label' => 'አፈጻጸም', 'value' => PerformanceReport::query()->where('user_id', $user?->id)->count(), 'meta' => 'የእኔ'],
                ['label' => 'ረቂቅ አፈጻጸም', 'value' => PerformanceReport::query()->where('user_id', $user?->id)->where('status', ReviewStatus::Draft)->count(), 'meta' => 'ያልቀረበ'],
                ['label' => 'ቀርቧል', 'value' => PerformanceReport::query()->where('user_id', $user?->id)->where('status', ReviewStatus::Submitted)->count(), 'meta' => 'በመጠባበቅ'],
            ],
            'shortcuts' => array_values(array_filter([
                self::link('አፈጻጸም', 'admin.performance.index'),
                self::link('ሳምንታዊ ሪፖርት', 'admin.weekly-reports.index'),
                self::link('ዓመታዊ እቅድ', 'admin.annual-plans.index'),
            ])),
            'show_waiting' => false,
            'show_matrix' => true,
            'show_mine' => true,
            'mine_label' => 'የእኔ አፈጻጸም',
        ];
    }

    /**
     * @param  list<int>  $ids
     * @return array<string, mixed>
     */
    private static function fallback(string $label, array $ids, ?User $user): array
    {
        return [
            'title' => 'የሰራተኛ ዳሽቦርድ',
            'subtitle' => 'የእርስዎ ስራ እና ፈጣን አገናኞች።',
            'role_label' => $label,
            'kpis' => [
                ['label' => 'አፈጻጸም', 'value' => PerformanceReport::query()->where('user_id', $user?->id)->count(), 'meta' => 'የእኔ'],
                ['label' => 'ተቋማት', 'value' => count($ids), 'meta' => 'በእይታ'],
                ['label' => 'ህጎች', 'value' => Law::query()->where('is_published', true)->count(), 'meta' => 'ታትሟል'],
                ['label' => 'ማሳወቂያ', 'value' => $user?->unreadNotifications()->count() ?? 0, 'meta' => 'ያልተነበቡ'],
            ],
            'shortcuts' => array_values(array_filter([
                self::link('አፈጻጸም', 'admin.performance.index'),
                self::link('ማሳወቂያ', 'notifications.index'),
            ])),
            'show_waiting' => false,
            'show_matrix' => false,
            'show_mine' => true,
            'mine_label' => 'የእኔ አፈጻጸም',
        ];
    }

    /**
     * @param  list<int>  $ids
     */
    private static function waitingTotal(array $ids): int
    {
        return PerformanceReport::query()->whereIn('organization_id', $ids)->where('status', ReviewStatus::Submitted)->count()
            + WeeklyReport::query()->whereIn('organization_id', $ids)->where('status', ReviewStatus::Submitted)->count()
            + AnnualPlan::query()->whereIn('organization_id', $ids)->where('status', ReviewStatus::Submitted)->count()
            + StrategicFocusData::query()->whereIn('organization_id', $ids)->where('status', ReviewStatus::Submitted)->count();
    }

    /**
     * @return array{label: string, url: string}|null
     */
    private static function link(string $label, string $route): ?array
    {
        if (! Route::has($route)) {
            return null;
        }

        return ['label' => $label, 'url' => route($route)];
    }
}
