<?php

namespace Tests\Feature;

use App\Models\CriminalCase;
use App\Models\Organization;
use App\Models\PerformanceReport;
use App\Models\User;
use App\Models\WorkUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectoryAndPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_zone_head_registers_edits_and_deactivates_a_woreda(): void
    {
        $head = User::query()->where('email', 'zone.head@yemjustice.test')->firstOrFail();
        $planner = User::query()->where('email', 'office.plan@yemjustice.test')->firstOrFail();
        $zone = Organization::query()->where('type', 'zone')->firstOrFail();

        $this->actingAs($planner)->get(route('admin.offices.index'))->assertForbidden();

        $this->actingAs($head)->post(route('admin.offices.store'), [
            'name' => 'የገራ ወረዳ ፍትህ ጽህፈት ቤት',
            'type' => 'woreda',
            'parent_id' => $zone->id,
            'is_active' => '1',
        ])->assertRedirect();

        $office = Organization::query()->where('name', 'የገራ ወረዳ ፍትህ ጽህፈት ቤት')->firstOrFail();
        $this->assertSame($zone->id, $office->parent_id);
        $this->assertTrue($office->is_active);
        $this->assertSame(4, $office->workUnits()->count());

        $this->actingAs($head)->put(route('admin.offices.update', $office), [
            'name' => 'የገራ ወረዳ ፍትህ ጽህፈት ቤት የተሻሻለ',
            'type' => 'woreda',
            'parent_id' => $zone->id,
            'is_active' => '1',
        ])->assertRedirect(route('admin.offices.show', $office));

        $this->actingAs($head)
            ->post(route('admin.offices.deactivate', $office))
            ->assertRedirect();

        $this->assertFalse($office->fresh()->is_active);
        $this->actingAs($head)
            ->get(route('admin.offices.show', $office))
            ->assertOk()
            ->assertSee('ጠፍቷል');

        $this->actingAs($head)->post(route('admin.offices.store'), [
            'name' => 'ሌላ ዞን',
            'type' => 'zone',
        ])->assertSessionHasErrors('type');
    }

    public function test_hr_registers_an_expert_and_deactivation_blocks_login(): void
    {
        $hr = User::query()->where('email', 'hr.expert@yemjustice.test')->firstOrFail();
        $legal = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();
        $unit = WorkUnit::query()->where('organization_id', $hr->organization_id)->firstOrFail();

        $this->actingAs($legal)->get(route('admin.experts.create'))->assertForbidden();

        $this->actingAs($hr)->post(route('admin.experts.store'), [
            'name' => 'ማርታ ተስፋዬ',
            'email' => 'marta.expert@yemjustice.test',
            'password' => 'secret-pass',
            'role' => 'legal_expert',
            'organization_id' => $hr->organization_id,
            'work_unit_id' => $unit->id,
            'is_active' => '1',
        ])->assertRedirect();

        $expert = User::query()->where('email', 'marta.expert@yemjustice.test')->firstOrFail();

        $this->actingAs($hr)->put(route('admin.experts.update', $expert), [
            'name' => 'ማርታ ተስፋዬ ጌታቸው',
            'email' => 'marta.expert@yemjustice.test',
            'role' => 'legal_expert',
            'organization_id' => $hr->organization_id,
            'work_unit_id' => $unit->id,
            'is_active' => '1',
        ])->assertRedirect(route('admin.experts.show', $expert));

        $this->assertSame('ማርታ ተስፋዬ ጌታቸው', $expert->fresh()->name);

        $this->actingAs($hr)->post(route('admin.experts.deactivate', $expert))->assertRedirect();
        $this->assertFalse($expert->fresh()->is_active);

        auth()->logout();

        $this->post(route('login.store'), [
            'email' => 'marta.expert@yemjustice.test',
            'password' => 'secret-pass',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_a_case_can_be_linked_to_an_expert(): void
    {
        $legal = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();
        $fofa = Organization::query()->where('name', 'like', '%ፎፋ%')->firstOrFail();

        $this->actingAs($legal)->post(route('admin.criminal-cases.store'), [
            'organization_id' => $fofa->id,
            'court_level' => 'woreda_court',
            'prosecutor_file_number' => 'YZ/ወ/300/2019',
            'defendant_names' => 'የተሳሰረ ተከሳሽ',
            'expert_id' => $legal->id,
        ])->assertRedirect(route('admin.criminal-cases.index'));

        $case = CriminalCase::query()->where('prosecutor_file_number', 'YZ/ወ/300/2019')->firstOrFail();
        $this->assertTrue($case->expert->is($legal));

        $this->actingAs($legal)
            ->get(route('admin.criminal-cases.index', ['q' => 'YZ/ወ/300/2019']))
            ->assertOk()
            ->assertSee($legal->displayName())
            ->assertSee('የተሳሰረ ባለሙያ');

        $hr = User::query()->where('email', 'hr.expert@yemjustice.test')->firstOrFail();
        $this->actingAs($hr)
            ->get(route('admin.experts.show', $legal))
            ->assertOk()
            ->assertSee('የወንጀል ጉዳይ፡ 1');
    }

    public function test_expert_submits_performance_and_only_a_head_can_approve_it(): void
    {
        $legal = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();
        $head = User::query()->where('email', 'zone.head@yemjustice.test')->firstOrFail();
        $office = User::query()->where('email', 'office.head@yemjustice.test')->firstOrFail();

        $this->actingAs($legal)->post(route('admin.performance.store'), [
            'action' => 'submit',
            'period' => 'monthly',
            'budget_year_ec' => 2019,
            'period_number' => 3,
            'period_label' => 'ህዳር',
            'accomplished' => 'ሦስት ጉዳዮች ተዘግተዋል',
        ])->assertRedirect();

        $report = PerformanceReport::query()->where('user_id', $legal->id)->firstOrFail();
        $this->assertSame('submitted', $report->status->value);
        $this->assertSame($legal->organization_id, $report->organization_id);

        $this->actingAs($legal)
            ->put(route('admin.performance.update', $report), [
                'action' => 'draft',
                'period' => 'monthly',
                'budget_year_ec' => 2019,
                'period_number' => 3,
                'accomplished' => 'አይቀየርም',
            ])->assertForbidden();

        $this->actingAs($legal)
            ->post(route('admin.performance.approve', $report))
            ->assertForbidden();

        $this->actingAs($office)
            ->get(route('admin.performance.show', $report))
            ->assertNotFound();

        $this->actingAs($head)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('ለማጽደቅ የቀረቡ ሪፖርቶች')
            ->assertSee($legal->displayName())
            ->assertSee('ወራዊ');

        $this->actingAs($office)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee($legal->displayName());

        $this->actingAs($head)
            ->get(route('admin.performance.index'))
            ->assertOk()
            ->assertSee($legal->displayName())
            ->assertSee('ወራዊ');

        $this->actingAs($head)
            ->get(route('admin.performance.show', $report))
            ->assertOk()
            ->assertSee('ሦስት ጉዳዮች ተዘግተዋል');

        $this->actingAs($head)
            ->post(route('admin.performance.approve', $report))
            ->assertRedirect();

        $this->assertSame('approved', $report->fresh()->status->value);
        $this->assertSame($head->id, $report->fresh()->reviewed_by);

        $this->actingAs($legal)->post(route('admin.performance.store'), [
            'action' => 'submit',
            'period' => 'quarterly',
            'budget_year_ec' => 2019,
            'period_number' => 5,
            'accomplished' => 'ከወሰን ውጭ',
        ])->assertSessionHasErrors('period_number');

        $this->actingAs($legal)->post(route('admin.performance.store'), [
            'action' => 'submit',
            'period' => 'semiannual',
            'budget_year_ec' => 2019,
            'period_number' => 1,
            'accomplished' => 'የመጀመሪያ ስድስት ወር ተጠናቋል',
        ])->assertRedirect();

        $this->actingAs($head)
            ->post(route('admin.performance.reject', PerformanceReport::query()->where('period', 'semiannual')->firstOrFail()), [
                'review_note' => 'ዝርዝር ይጨምሩ',
            ])->assertRedirect();

        $rejected = PerformanceReport::query()->where('period', 'semiannual')->firstOrFail();
        $this->assertSame('rejected', $rejected->status->value);

        $this->actingAs($legal)->put(route('admin.performance.update', $rejected), [
            'action' => 'submit',
            'period' => 'semiannual',
            'budget_year_ec' => 2019,
            'period_number' => 1,
            'accomplished' => 'ዝርዝር ተጨምሯል',
        ])->assertRedirect(route('admin.performance.show', $rejected));

        $this->assertSame('submitted', $rejected->fresh()->status->value);
    }
}
