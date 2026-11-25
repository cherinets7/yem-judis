<?php

namespace Tests\Feature;

use App\Models\CriminalCase;
use App\Models\Organization;
use App\Models\PowerOfAttorneyGrant;
use App\Models\PublicCaseTracking;
use App\Models\User;
use App\Notifications\PasswordResetRequested;
use App\Notifications\ReportSubmitted;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_a_forgotten_password_emails_and_heads_can_set_a_new_one(): void
    {
        $legal = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();
        $head = User::query()->where('email', 'zone.head@yemjustice.test')->firstOrFail();
        $hr = User::query()->where('email', 'hr.expert@yemjustice.test')->firstOrFail();

        Notification::fake();

        $this->post(route('password.email'), ['email' => $legal->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($legal, ResetPassword::class);
        Notification::assertSentTo($head, PasswordResetRequested::class);
        Notification::assertSentTo($hr, PasswordResetRequested::class);

        $this->actingAs($hr)->post(route('admin.experts.password', $legal), [
            'password' => 'desk-secret',
            'password_confirmation' => 'desk-secret',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertTrue(Hash::check('desk-secret', $legal->fresh()->password));

        auth()->logout();
        $this->post(route('login.store'), [
            'email' => $legal->email,
            'password' => 'desk-secret',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_a_weekly_report_notifies_the_head_and_appears_on_the_dashboard(): void
    {
        $planner = User::query()->where('email', 'office.plan@yemjustice.test')->firstOrFail();
        $officeHead = User::query()->where('email', 'office.head@yemjustice.test')->firstOrFail();
        $zoneHead = User::query()->where('email', 'zone.head@yemjustice.test')->firstOrFail();

        $this->actingAs($planner)->post(route('admin.weekly-reports.store'), [
            'work_unit_id' => $planner->work_unit_id,
            'budget_year_ec' => 2016,
            'week_number' => 7,
            'status' => 'submitted',
            'planned_activities' => 'ክትትል',
            'accomplished_activities' => 'ተከናውኗል',
        ])->assertRedirect();

        $this->assertSame(1, $officeHead->fresh()->unreadNotifications()->count());
        $this->assertSame(1, $zoneHead->fresh()->unreadNotifications()->count());
        $this->assertSame(ReportSubmitted::class, $zoneHead->notifications()->first()->type);

        $this->actingAs($zoneHead)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('ለማጽደቅ የቀረቡ ሪፖርቶች')
            ->assertSee('ሳምንታዊ');
    }

    public function test_registers_search_files_and_appointments_and_office_transfer(): void
    {
        Storage::fake('local');
        $legal = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();
        $head = User::query()->where('email', 'zone.head@yemjustice.test')->firstOrFail();
        $saja = Organization::query()->where('name', 'like', '%ሳጃ%')->firstOrFail();
        $zone = Organization::query()->where('type', 'zone')->firstOrFail();

        $this->actingAs($legal)->post(route('admin.criminal-cases.store'), [
            'organization_id' => $saja->id,
            'court_level' => 'woreda_court',
            'prosecutor_file_number' => 'YZ/ወ/990/2019',
            'defendant_names' => 'ቀጠሮ ተከሳሽ',
            'appointment_date_ec' => '12/04/2019',
            'appointment_reason' => 'ምስክር ለመስማት',
        ])->assertRedirect();

        $case = CriminalCase::query()->where('prosecutor_file_number', 'YZ/ወ/990/2019')->firstOrFail();
        $this->actingAs($legal)
            ->post(route('admin.criminal-cases.publish', $case), ['is_published' => '1'])
            ->assertRedirect();

        $tracking = PublicCaseTracking::query()->where('prosecutor_file_number', 'YZ/ወ/990/2019')->firstOrFail();
        $this->assertSame('12/04/2019', $tracking->appointment_date_ec);
        $this->assertSame('ምስክር ለመስማት', $tracking->appointment_reason);

        auth()->logout();
        $this->get('/cases-tracking?q='.urlencode('YZ/ወ/990/2019'))
            ->assertOk()
            ->assertSee('12/04/2019')
            ->assertSee('ምስክር ለመስማት');

        $this->actingAs($legal)->post(route('admin.poa-grants.store'), [
            'grantor_name' => 'ውክልና ሰጪ',
            'grantee_name' => 'ውክልና ተቀባይ',
            'registration_number' => 'POA-990',
        ])->assertRedirect();

        $grant = PowerOfAttorneyGrant::query()->where('registration_number', 'POA-990')->firstOrFail();
        $this->actingAs($legal)->post(route('admin.poa-grants.documents.store', $grant), [
            'title' => 'የውክልና ቅጽ',
            'file' => UploadedFile::fake()->create('poa.pdf', 12, 'application/pdf'),
        ])->assertRedirect();

        $this->assertSame(1, $grant->documents()->count());

        $this->actingAs($legal)
            ->get(route('admin.search', ['q' => 'YZ/ወ/990/2019']))
            ->assertOk()
            ->assertSee('YZ/ወ/990/2019')
            ->assertSee('የወንጀል ጉዳይ');

        $this->actingAs($legal)
            ->get(route('admin.search', ['q' => 'POA-990']))
            ->assertOk()
            ->assertSee('POA-990')
            ->assertSee('ውክልና');

        $this->actingAs($head)
            ->from(route('admin.offices.show', $saja))
            ->post(route('admin.offices.deactivate', $saja))
            ->assertSessionHasErrors('transfer_to');

        $this->assertTrue($saja->fresh()->is_active);
        $this->assertSame($saja->id, $case->fresh()->organization_id);

        $this->actingAs($head)->post(route('admin.offices.deactivate', $saja), [
            'transfer_to' => $zone->id,
        ])->assertRedirect();

        $this->assertFalse($saja->fresh()->is_active);
        $this->assertSame($zone->id, $case->fresh()->organization_id);
        $this->assertSame($zone->id, $grant->fresh()->organization_id);
        $this->assertSame($zone->id, $tracking->fresh()->organization_id);
    }
}
