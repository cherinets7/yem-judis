<?php

namespace Tests\Feature;

use App\Models\AnnualPlan;
use App\Models\CommunityPost;
use App\Models\CriminalCase;
use App\Models\Law;
use App\Models\Organization;
use App\Models\StaffProfile;
use App\Models\User;
use App\Models\WeeklyReport;
use App\Support\RegisterCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_guest_is_sent_to_login_and_inactive_accounts_stay_out(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));

        $this->post(route('login.store'), [
            'email' => 'zone.head@yemjustice.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->post(route('logout'))->assertRedirect(route('home'));

        $head = User::query()->where('email', 'zone.head@yemjustice.test')->firstOrFail();
        $head->forceFill(['is_active' => false])->save();

        $this->post(route('login.store'), [
            'email' => 'zone.head@yemjustice.test',
            'password' => 'password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $head->forceFill(['is_active' => true])->save();
        $this->actingAs($head)->get(route('dashboard'))->assertOk();
        $head->forceFill(['is_active' => false])->save();
        $this->actingAs($head->fresh())->get(route('dashboard'))->assertForbidden();
    }

    public function test_dashboard_scope_follows_the_signed_in_office(): void
    {
        $office = User::query()->where('email', 'office.head@yemjustice.test')->firstOrFail();
        $zone = User::query()->where('email', 'zone.head@yemjustice.test')->firstOrFail();

        $this->actingAs($office)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('ዐቃቤ ህጎች')
            ->assertSee('ፐብሊክ ሰርቫንት')
            ->assertSee('የሁለቱ ድምር')
            ->assertSee('ጠቅላላ ህጎች')
            ->assertSee('የሳጃ')
            ->assertSee('ጸድቋል')
            ->assertSee('ዐቃቤ ህግ ሳምንታዊ')
            ->assertDontSee('አልቀረበም')
            ->assertDontSee('የፎፋ')
            ->assertDontSee('ዞን ማዕከል');

        $this->actingAs($office)->get(route('admin.staff-profiles.index'))
            ->assertOk()
            ->assertSee('ሙሉጌታ ተፈራ')
            ->assertDontSee('ታሪኩ ሻሎ');

        $this->actingAs($office)->get(route('woredas'))
            ->assertOk()
            ->assertSee('የሳጃ')
            ->assertDontSee('የፎፋ');

        $this->actingAs($office)->get(route('zone-center'))->assertForbidden();

        $this->actingAs($zone)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('የፎፋ')
            ->assertSee('የሳጃ')
            ->assertSee('የየም ዞን')
            ->assertSee('አልቀረበም');

        $this->actingAs($zone)->get(route('zone-center'))
            ->assertOk()
            ->assertSee('የፎፋ')
            ->assertSee('የቶባ');
    }

    public function test_every_register_screen_renders_for_a_super_admin(): void
    {
        $admin = User::query()->where('email', 'super.admin@yemjustice.test')->firstOrFail();

        foreach (RegisterCatalog::all() as $definition) {
            $this->actingAs($admin)
                ->get(route('admin.'.$definition['uri'].'.index'))
                ->assertOk()
                ->assertSee($definition['title']);

            $this->actingAs($admin)
                ->get(route('admin.'.$definition['uri'].'.create'))
                ->assertOk()
                ->assertSee('መዝግብ');
        }
    }

    public function test_office_plan_expert_submits_a_plan_and_only_a_head_can_approve_it(): void
    {
        $planner = User::query()->where('email', 'office.plan@yemjustice.test')->firstOrFail();
        $zonePlan = User::query()->where('email', 'zone.plan@yemjustice.test')->firstOrFail();
        $zoneHead = User::query()->where('email', 'zone.head@yemjustice.test')->firstOrFail();

        $this->actingAs($planner)->post(route('admin.annual-plans.store'), [
            'budget_year_ec' => 2019,
            'title' => 'የሳጃ ዓመታዊ እቅድ',
            'document_type' => 'annual_plan',
            'status' => 'submitted',
            'summary' => 'ማጠቃለያ',
        ])->assertRedirect(route('admin.annual-plans.index'));

        $plan = AnnualPlan::query()->where('title', 'የሳጃ ዓመታዊ እቅድ')->firstOrFail();
        $this->assertSame($planner->organization_id, $plan->organization_id);
        $this->assertSame($planner->id, $plan->user_id);
        $this->assertSame('submitted', $plan->status->value);

        $this->actingAs($planner)
            ->from(route('admin.annual-plans.create'))
            ->post(route('admin.annual-plans.store'), ['status' => 'submitted'])
            ->assertRedirect(route('admin.annual-plans.create'))
            ->assertSessionHasErrors('title');

        $this->actingAs($zonePlan)
            ->post(route('admin.annual-plans.approve', $plan))
            ->assertForbidden();

        $this->actingAs($zoneHead)
            ->post(route('admin.annual-plans.approve', $plan), ['review_note' => 'ተመልክቷል'])
            ->assertRedirect();

        $plan->refresh();
        $this->assertSame('approved', $plan->status->value);
        $this->assertSame($zoneHead->id, $plan->reviewed_by);
        $this->assertSame('ተመልክቷል', $plan->review_note);

        $this->actingAs($planner)->put(route('admin.annual-plans.update', $plan), [
            'budget_year_ec' => 2019,
            'title' => 'የተሻሻለ እቅድ',
            'document_type' => 'annual_report',
            'status' => 'draft',
        ])->assertRedirect(route('admin.annual-plans.edit', $plan));

        $this->assertSame('approved', $plan->fresh()->status->value);
        $this->assertSame('የተሻሻለ እቅድ', $plan->fresh()->title);

        $this->actingAs($planner)
            ->delete(route('admin.annual-plans.destroy', $plan))
            ->assertRedirect(route('admin.annual-plans.index'));
        $this->assertModelMissing($plan);
    }

    public function test_weekly_report_rejects_a_duplicate_week_for_the_same_unit(): void
    {
        $planner = User::query()->where('email', 'office.plan@yemjustice.test')->firstOrFail();
        $payload = [
            'work_unit_id' => $planner->work_unit_id,
            'budget_year_ec' => 2019,
            'week_number' => 12,
            'status' => 'draft',
            'planned_activities' => 'ዕቅድ',
        ];

        $this->actingAs($planner)
            ->post(route('admin.weekly-reports.store'), $payload)
            ->assertRedirect(route('admin.weekly-reports.index'));

        $this->actingAs($planner)
            ->from(route('admin.weekly-reports.create'))
            ->post(route('admin.weekly-reports.store'), $payload)
            ->assertSessionHasErrors('week_number');

        $report = WeeklyReport::query()->where('week_number', 12)->where('user_id', $planner->id)->firstOrFail();
        $this->actingAs($planner)->put(route('admin.weekly-reports.update', $report), [
            ...$payload,
            'status' => 'submitted',
            'planned_activities' => 'የተሻሻለ',
        ])->assertRedirect(route('admin.weekly-reports.edit', $report));

        $this->assertSame('የተሻሻለ', $report->fresh()->planned_activities);
    }

    public function test_legal_expert_records_a_case_inside_scope_and_an_office_cannot_open_it(): void
    {
        $legal = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();
        $office = User::query()->where('email', 'office.head@yemjustice.test')->firstOrFail();
        $hr = User::query()->where('email', 'hr.expert@yemjustice.test')->firstOrFail();
        $fofa = Organization::query()->where('name', 'like', '%ፎፋ%')->firstOrFail();

        $this->actingAs($hr)->get(route('admin.criminal-cases.index'))->assertForbidden();

        $this->actingAs($legal)->post(route('admin.criminal-cases.store'), [
            'organization_id' => $fofa->id,
            'court_level' => 'woreda_court',
            'prosecutor_file_number' => 'YZ/ወ/100/2019',
            'defendant_names' => 'ሙከራ ተከሳሽ',
        ])->assertRedirect(route('admin.criminal-cases.index'));

        $case = CriminalCase::query()->where('prosecutor_file_number', 'YZ/ወ/100/2019')->firstOrFail();
        $this->assertSame($fofa->id, $case->organization_id);
        $this->assertSame($legal->id, $case->created_by);

        $this->actingAs($legal)
            ->get(route('admin.criminal-cases.index', ['q' => 'ሙከራ ተከሳሽ']))
            ->assertOk()
            ->assertSee('YZ/ወ/100/2019');

        $this->actingAs($legal)
            ->get(route('admin.criminal-cases.index', ['court_level' => 'high_court']))
            ->assertOk()
            ->assertDontSee('YZ/ወ/100/2019');

        $this->actingAs($office)
            ->get(route('admin.criminal-cases.edit', $case))
            ->assertNotFound();

        $this->actingAs($office)
            ->post(route('admin.criminal-cases.store'), [
                'organization_id' => $fofa->id,
                'court_level' => 'woreda_court',
                'prosecutor_file_number' => 'YZ/ወ/200/2019',
            ])->assertSessionHasErrors('organization_id');
    }

    public function test_hr_saves_staff_history_rows_and_drops_empty_ones(): void
    {
        $hr = User::query()->where('email', 'hr.expert@yemjustice.test')->firstOrFail();

        $this->actingAs($hr)->post(route('admin.staff-profiles.store'), [
            'staff_category' => 'prosecutor',
            'full_name' => 'ትዕግስት አበበ',
            'salary_history' => [
                ['year_ec' => '2015', 'amount' => '9000', 'note' => 'መነሻ'],
                ['year_ec' => '', 'amount' => '', 'note' => ''],
            ],
            'performance_evaluations' => [
                ['year_ec' => '', 'result' => '', 'note' => ''],
            ],
            'promotions' => [
                ['year_ec' => '2018', 'from_title' => 'ረዳት', 'to_title' => 'ዐቃቤ ህግ'],
            ],
        ])->assertRedirect(route('admin.staff-profiles.index'));

        $profile = StaffProfile::query()->where('full_name', 'ትዕግስት አበበ')->firstOrFail();
        $this->assertSame($hr->organization_id, $profile->organization_id);
        $this->assertSame($hr->id, $profile->filled_by_expert);
        $this->assertCount(1, $profile->salary_history);
        $this->assertEquals(2015, $profile->salary_history[0]['year_ec']);
        $this->assertSame('መነሻ', $profile->salary_history[0]['note']);
        $this->assertNull($profile->performance_evaluations);
        $this->assertCount(1, $profile->promotions);
        $this->assertSame('ዐቃቤ ህግ', $profile->promotions[0]['to_title']);

        $prosecutors = StaffProfile::query()->where('staff_category', 'prosecutor')->count();

        $this->actingAs($hr)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('>'.$prosecutors.'<', false);
    }

    public function test_legal_expert_hides_a_public_post_and_uploads_a_law(): void
    {
        Storage::fake('local');

        $legal = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();
        $planner = User::query()->where('email', 'office.plan@yemjustice.test')->firstOrFail();
        $post = CommunityPost::query()->where('type', 'news')->where('is_published', true)->firstOrFail();

        $this->actingAs($planner)->get(route('admin.laws.index'))->assertForbidden();

        $this->actingAs($legal)
            ->post(route('admin.community-posts.publish', $post), ['is_published' => '0'])
            ->assertRedirect();

        $this->assertFalse($post->fresh()->is_published);
        $this->get(route('home'))->assertDontSee($post->title);

        $this->actingAs($legal)->post(route('admin.laws.store'), [
            'jurisdiction' => 'zonal',
            'category' => 'directive',
            'title' => 'የሙከራ መመሪያ',
            'file_path' => UploadedFile::fake()->create('law.txt', 12, 'text/plain'),
        ])->assertRedirect(route('admin.laws.index'));

        $law = Law::query()->where('title', 'የሙከራ መመሪያ')->firstOrFail();
        $this->assertSame($legal->id, $law->uploaded_by);
        Storage::disk('local')->assertExists($law->file_path);
        $this->assertStringStartsWith('laws/', $law->file_path);
    }
}
