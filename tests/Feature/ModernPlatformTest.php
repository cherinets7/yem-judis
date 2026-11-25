<?php

namespace Tests\Feature;

use App\Models\CriminalCase;
use App\Models\User;
use App\Notifications\ReportSubmitted;
use App\Support\TwoFactor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class ModernPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_api_login_search_and_notifications_work_with_sanctum(): void
    {
        $legal = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();

        $this->actingAs($legal)->post(route('admin.criminal-cases.store'), [
            'court_level' => 'woreda_court',
            'prosecutor_file_number' => 'YZ/ወ/992/2019',
            'defendant_names' => 'API ተከሳሽ',
        ])->assertRedirect();

        auth()->logout();

        $login = $this->postJson('/api/login', [
            'email' => $legal->email,
            'password' => 'password',
            'device_name' => 'test-phone',
        ])->assertOk()
            ->assertJsonPath('user.email', $legal->email);

        $token = $login->json('token');

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.id', $legal->id);

        $this->withToken($token)
            ->getJson('/api/search?q='.urlencode('YZ/ወ/992/2019'))
            ->assertOk()
            ->assertJsonFragment(['title' => 'YZ/ወ/992/2019']);

        $this->withToken($token)
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonStructure(['unread', 'notifications']);
    }

    public function test_two_factor_blocks_login_until_the_code_is_confirmed(): void
    {
        $head = User::query()->where('email', 'zone.head@yemjustice.test')->firstOrFail();
        $secret = TwoFactor::makeSecret();
        $head->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => ['abcd-efgh'],
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->post(route('login.store'), [
            'email' => $head->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->assertGuest();

        $code = (new Google2FA)->getCurrentOtp($secret);

        $this->post(route('two-factor.confirm'), ['code' => $code])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($head->fresh());
    }

    public function test_documents_use_the_configured_disk_and_report_mail_is_queued(): void
    {
        Storage::fake('local');
        Mail::fake();
        Notification::fake();

        $legal = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();
        $planner = User::query()->where('email', 'office.plan@yemjustice.test')->firstOrFail();
        $head = User::query()->where('email', 'zone.head@yemjustice.test')->firstOrFail();

        $this->actingAs($legal)->post(route('admin.criminal-cases.store'), [
            'court_level' => 'woreda_court',
            'prosecutor_file_number' => 'YZ/ወ/993/2019',
            'defendant_names' => 'ዲስክ ተከሳሽ',
        ])->assertRedirect();

        $case = CriminalCase::query()->where('prosecutor_file_number', 'YZ/ወ/993/2019')->firstOrFail();

        $this->actingAs($legal)->post(route('admin.criminal-cases.documents.store', $case), [
            'title' => 'የዲስክ ፋይል',
            'file' => UploadedFile::fake()->create('disk.pdf', 12, 'application/pdf'),
        ])->assertRedirect();

        $document = $case->documents()->firstOrFail();
        $this->assertSame('local', $document->disk);
        Storage::disk('local')->assertExists($document->file_path);

        $this->actingAs($planner)->post(route('admin.weekly-reports.store'), [
            'work_unit_id' => $planner->work_unit_id,
            'budget_year_ec' => 2014,
            'week_number' => 4,
            'status' => 'submitted',
            'planned_activities' => 'ክትትል',
            'accomplished_activities' => 'ተከናውኗል',
        ])->assertRedirect();

        Notification::assertSentTo($head, ReportSubmitted::class);

        $this->actingAs($head)
            ->getJson(route('notifications.poll'))
            ->assertOk()
            ->assertJsonStructure(['unread', 'latest']);
    }

    public function test_backup_command_writes_a_sqlite_copy(): void
    {
        if (config('database.default') !== 'sqlite') {
            $this->markTestSkipped('SQLite only in this suite.');
        }

        $this->artisan('justice:backup')->assertSuccessful();
    }
}
