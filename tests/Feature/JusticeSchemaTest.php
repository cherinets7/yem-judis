<?php

namespace Tests\Feature;

use App\Enums\CourtLevel;
use App\Enums\FinalOutcome;
use App\Enums\OrganizationType;
use App\Enums\ProsecutorReportType;
use App\Enums\PublicCaseType;
use App\Enums\ReviewStatus;
use App\Enums\StaffCategory;
use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\ProsecutorWeeklyReport;
use App\Models\PublicCaseTracking;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JusticeSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_builds_the_zone_offices_units_and_every_role(): void
    {
        $this->seed();

        $zone = Organization::query()->where('type', OrganizationType::Zone)->first();

        $this->assertNotNull($zone);
        $this->assertStringContainsString('የየም ዞን ፍትህ መምሪያ', $zone->name);
        $this->assertSame(4, $zone->children()->count());
        $this->assertSame(1, $zone->children()->where('type', OrganizationType::City)->count());
        $this->assertSame(3, $zone->children()->where('type', OrganizationType::Woreda)->count());
        $this->assertSame(6, $zone->workUnits()->count());

        $city = $zone->children()->where('type', OrganizationType::City)->first();
        $this->assertSame(4, $city->workUnits()->count());
        $this->assertTrue($city->parent->is($zone));

        $this->assertSame(8, User::query()->count());

        foreach (UserRole::cases() as $role) {
            $user = User::query()->where('role', $role)->first();
            $this->assertNotNull($user, $role->value);
            $this->assertTrue($user->is_active);
            $this->assertTrue(Hash::check('password', $user->password));
            $this->assertNotNull($user->organization);
        }

        $officeHead = User::query()->where('role', UserRole::OfficeHead)->first();
        $this->assertSame(OrganizationType::City, $officeHead->organization->type);
        $this->assertTrue($officeHead->organization->parent->is($zone));
        $this->assertNotNull($officeHead->workUnit);
        $this->assertTrue($officeHead->workUnit->organization->is($officeHead->organization));
    }

    public function test_json_boolean_and_enum_casts_round_trip(): void
    {
        $this->seed();

        $expert = User::query()->where('role', UserRole::HrExpert)->first();

        $profile = StaffProfile::query()->create([
            'organization_id' => $expert->organization_id,
            'work_unit_id' => $expert->work_unit_id,
            'staff_category' => StaffCategory::Prosecutor,
            'full_name' => 'ዳኛ ተስፋዬ አበበ',
            'salary_history' => [
                ['year_ec' => 2015, 'amount' => '8000.00'],
            ],
            'performance_evaluations' => [
                ['year_ec' => 2017, 'result' => 'በጣም ጥሩ'],
            ],
            'promotions' => [],
            'created_by' => $expert->id,
            'filled_by_expert' => $expert->id,
        ]);

        $profile->refresh();

        $this->assertSame(StaffCategory::Prosecutor, $profile->staff_category);
        $this->assertSame(2015, $profile->salary_history[0]['year_ec']);
        $this->assertSame('በጣም ጥሩ', $profile->performance_evaluations[0]['result']);
        $this->assertSame([], $profile->promotions);
        $this->assertTrue($profile->filledByExpert->is($expert));
        $this->assertTrue($profile->creator->is($expert));
        $this->assertTrue($profile->organization->staffProfiles->contains($profile));

        $report = ProsecutorWeeklyReport::query()->create([
            'organization_id' => $expert->organization_id,
            'user_id' => $expert->id,
            'report_type' => ProsecutorReportType::Criminal,
            'budget_year_ec' => 2018,
            'week_number' => 12,
            'period_covered_ec' => '2018-03-01 እስከ 2018-03-07',
            'status' => ReviewStatus::Submitted,
        ]);

        $item = $report->items()->create([
            'section_code' => '001',
            'row_label' => 'የወረዳ ፍርድ ቤት ወንጀል ጉዳዮች',
            'metrics' => ['opened' => 4, 'closed' => 2],
        ]);

        $item->refresh();

        $this->assertSame(['opened' => 4, 'closed' => 2], $item->metrics);
        $this->assertTrue($item->report->is($report));
        $this->assertSame(ReviewStatus::Submitted, $report->fresh()->status);
        $this->assertSame(ProsecutorReportType::Criminal, $report->fresh()->report_type);

        $tracking = PublicCaseTracking::query()->create([
            'organization_id' => $expert->organization_id,
            'prosecutor_file_number' => 'YZ-001-2018',
            'plaintiff_name' => 'ዐቃቤ ህግ',
            'defendant_name' => 'ተከሳሽ አበበ',
            'case_type' => PublicCaseType::CriminalRegular,
            'court_level' => CourtLevel::WoredaCourt,
            'final_outcome' => FinalOutcome::Pending,
            'is_published' => true,
            'updated_by' => $expert->id,
        ]);

        $tracking->refresh();

        $this->assertTrue($tracking->is_published);
        $this->assertSame(FinalOutcome::Pending, $tracking->final_outcome);
        $this->assertSame(CourtLevel::WoredaCourt, $tracking->court_level);
        $this->assertTrue($tracking->updater->is($expert));
    }
}
