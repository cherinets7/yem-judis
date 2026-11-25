<?php

namespace Tests\Feature;

use App\Models\CriminalCase;
use App\Models\Organization;
use App\Models\StaffProfile;
use App\Models\User;
use App\Models\WorkUnit;
use App\Notifications\ReportSubmitted;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DailyOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_a_user_can_change_and_reset_a_password_and_login_locks_after_repeated_failures(): void
    {
        $legal = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();

        $this->actingAs($legal)->put(route('password.change'), [
            'current_password' => 'password',
            'password' => 'new-secret',
            'password_confirmation' => 'new-secret',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertTrue(Hash::check('new-secret', $legal->fresh()->password));

        auth()->logout();
        Notification::fake();

        $this->post(route('password.email'), ['email' => $legal->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($legal, ResetPassword::class);

        $token = Password::broker()->createToken($legal->fresh());

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $legal->email,
            'password' => 'reset-secret',
            'password_confirmation' => 'reset-secret',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('reset-secret', $legal->fresh()->password));

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => $legal->email,
                'password' => 'wrong-pass',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), [
            'email' => $legal->email,
            'password' => 'reset-secret',
        ])->assertSessionHasErrors('email');
    }

    public function test_work_units_can_be_added_and_an_expert_opens_a_staff_profile(): void
    {
        $head = User::query()->where('email', 'zone.head@yemjustice.test')->firstOrFail();
        $hr = User::query()->where('email', 'hr.expert@yemjustice.test')->firstOrFail();
        $zone = Organization::query()->where('type', 'zone')->firstOrFail();
        $unit = WorkUnit::query()->where('organization_id', $hr->organization_id)->firstOrFail();

        $this->actingAs($head)->post(route('admin.offices.units.store', $zone), [
            'name' => 'የቅሬታ ክፍል',
        ])->assertRedirect();

        $created = WorkUnit::query()->where('name', 'የቅሬታ ክፍል')->firstOrFail();
        $this->assertTrue($created->is_active);

        $this->actingAs($head)->post(route('admin.offices.units.deactivate', [$zone, $created]))->assertRedirect();
        $this->assertFalse($created->fresh()->is_active);

        $this->actingAs($hr)->post(route('admin.experts.store'), [
            'name' => 'ሊዲያ ኃይሌ',
            'email' => 'lidya.expert@yemjustice.test',
            'password' => 'secret-pass',
            'role' => 'legal_expert',
            'organization_id' => $hr->organization_id,
            'work_unit_id' => $unit->id,
            'is_active' => '1',
        ])->assertRedirect();

        $expert = User::query()->where('email', 'lidya.expert@yemjustice.test')->firstOrFail();
        $profile = StaffProfile::query()->where('user_id', $expert->id)->firstOrFail();
        $this->assertSame('ሊዲያ ኃይሌ', $profile->full_name);
        $this->assertSame('prosecutor', $profile->staff_category->value);

        $this->actingAs($hr)->put(route('admin.experts.update', $expert), [
            'name' => 'ሊዲያ ኃይሌ ተስፋ',
            'email' => $expert->email,
            'role' => 'legal_expert',
            'organization_id' => $hr->organization_id,
            'work_unit_id' => $unit->id,
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertSame('ሊዲያ ኃይሌ ተስፋ', $profile->fresh()->full_name);
    }

    public function test_deactivating_an_expert_moves_their_cases_and_requires_a_successor(): void
    {
        $legal = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();
        $hr = User::query()->where('email', 'hr.expert@yemjustice.test')->firstOrFail();
        $successor = User::query()->where('email', 'unit.coordinator@yemjustice.test')->firstOrFail();

        $this->actingAs($legal)->post(route('admin.criminal-cases.store'), [
            'court_level' => 'woreda_court',
            'prosecutor_file_number' => 'YZ/ወ/770/2019',
            'defendant_names' => 'የሚዘዋወር ተከሳሽ',
            'expert_id' => $legal->id,
        ])->assertRedirect();

        $case = CriminalCase::query()->where('prosecutor_file_number', 'YZ/ወ/770/2019')->firstOrFail();

        $this->actingAs($hr)
            ->from(route('admin.experts.show', $legal))
            ->post(route('admin.experts.deactivate', $legal))
            ->assertSessionHasErrors('transfer_to');

        $this->assertTrue($legal->fresh()->is_active);
        $this->assertSame($legal->id, $case->fresh()->expert_id);

        $this->actingAs($hr)->post(route('admin.experts.deactivate', $legal), [
            'transfer_to' => $successor->id,
        ])->assertRedirect();

        $this->assertFalse($legal->fresh()->is_active);
        $this->assertSame($successor->id, $case->fresh()->expert_id);
    }

    public function test_a_case_has_a_reading_page_a_file_and_an_audit_entry(): void
    {
        Storage::fake('local');
        $legal = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();

        $this->actingAs($legal)->post(route('admin.criminal-cases.store'), [
            'court_level' => 'woreda_court',
            'prosecutor_file_number' => 'YZ/ወ/771/2019',
            'defendant_names' => 'የፋይል ተከሳሽ',
        ])->assertRedirect();

        $case = CriminalCase::query()->where('prosecutor_file_number', 'YZ/ወ/771/2019')->firstOrFail();

        $this->actingAs($legal)
            ->get(route('admin.criminal-cases.show', $case))
            ->assertOk()
            ->assertSee('የፋይል ተከሳሽ')
            ->assertSee('ተመዝግቧል');

        $this->actingAs($legal)->post(route('admin.criminal-cases.documents.store', $case), [
            'title' => 'የፍርድ ቤት ውሳኔ',
            'file' => UploadedFile::fake()->create('decision.pdf', 20, 'application/pdf'),
        ])->assertRedirect();

        $document = $case->documents()->firstOrFail();
        Storage::disk('local')->assertExists($document->file_path);

        $this->actingAs($legal)
            ->get(route('admin.criminal-cases.documents.download', ['record' => $case, 'document' => $document]))
            ->assertOk();

        $this->actingAs($legal)
            ->get(route('admin.audit.index'))
            ->assertForbidden();
    }

    public function test_submitted_performance_notifies_the_head_and_exports(): void
    {
        $legal = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();
        $head = User::query()->where('email', 'zone.head@yemjustice.test')->firstOrFail();
        $office = User::query()->where('email', 'office.head@yemjustice.test')->firstOrFail();

        $this->actingAs($legal)->post(route('admin.performance.store'), [
            'action' => 'submit',
            'period' => 'weekly',
            'budget_year_ec' => 2017,
            'period_number' => 9,
            'accomplished' => 'ዘጠኝ ጉዳዮች ተከታትለዋል',
        ])->assertRedirect();

        $this->assertSame(1, $head->fresh()->unreadNotifications()->count());
        $this->assertSame(0, $office->fresh()->unreadNotifications()->count());

        $note = $head->notifications()->firstOrFail();
        $this->assertSame(ReportSubmitted::class, $note->type);

        $this->actingAs($head)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('አፈጻጸም አቅርቧል');

        $this->actingAs($head)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('የሪፖርት ድምር');

        $report = $legal->performanceReports()->firstOrFail();

        $this->actingAs($legal)
            ->get(route('admin.performance.pdf', $report))
            ->assertOk();

        $this->actingAs($legal)
            ->get(route('admin.performance.excel', $report))
            ->assertOk();
    }
}
