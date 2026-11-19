<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CommunityPost;
use App\Models\Law;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PublicPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_home_shows_published_news_announcements_and_laws(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Noto Sans Ethiopic', false)
            ->assertSee('bootstrap@5.3.3', false)
            ->assertSee('የጉዳዮች አስተዳደር')
            ->assertSee('የጉዳይ ክትትል በመስመር ላይ ተከፈተ')
            ->assertSee('የሰነድ ምዝገባ የአገልግሎት ሰዓት')
            ->assertSee('የወንጀለኛ መቅጫ ሕግ')
            ->assertDontSee('ያልታተመ ረቂቅ ዜና');
    }

    public function test_case_search_and_type_filter_hide_unpublished_rows(): void
    {
        $this->get('/cases-tracking?q='.urlencode('አበበ ከበደ'))
            ->assertOk()
            ->assertSee('YZ/ወ/001/2018')
            ->assertDontSee('YZ/ፍ/014/2018')
            ->assertDontSee('ያልታተመ ተከሳሽ');

        $this->get('/cases-tracking?q='.urlencode('ሳ/ፍ/088/2018'))
            ->assertOk()
            ->assertSee('ወ/ሮ አበበች ግርማ');

        $this->get('/cases-tracking?case_type=execution')
            ->assertOk()
            ->assertSee('YZ/ት/007/2018')
            ->assertSee('ፍርድ ትግበራ')
            ->assertDontSee('YZ/ወ/001/2018');

        $this->get('/cases-tracking?q='.urlencode('ያልታተመ'))
            ->assertOk()
            ->assertDontSee('YZ/ሚስጥር/999');
    }

    public function test_laws_can_be_filtered_and_downloaded(): void
    {
        $this->get('/laws?jurisdiction=zonal&category=directive')
            ->assertOk()
            ->assertSee('የየም ዞን የጉዳይ ሪፖርት መመሪያ')
            ->assertSee('ዞናዊ')
            ->assertSee('መመሪያ')
            ->assertDontSee('የወንጀለኛ መቅጫ ሕግ');

        $law = Law::query()->where('law_number', 'አዋጅ 414/1996')->firstOrFail();

        $response = $this->get(route('laws.download', $law));
        $response->assertOk()->assertDownload();
        $this->assertStringContainsString('የወንጀለኛ መቅጫ ሕግ', $response->streamedContent());

        $missing = Law::query()->create([
            'jurisdiction' => 'federal',
            'category' => 'manual',
            'title' => 'ፋይል የሌለው ማኑዋል',
            'file_path' => 'laws/missing-file.txt',
        ]);

        $this->get(route('laws.download', $missing))->assertNotFound();
    }

    public function test_community_tabs_and_public_feedback(): void
    {
        $this->get('/community?tab=jokes')
            ->assertOk()
            ->assertSee('የህግ ቀልዶች')
            ->assertSee('ከፍርድ ቤት ውጭ')
            ->assertDontSee('የጉዳይ ክትትል በመስመር ላይ ተከፈተ');

        $this->post('/community/feedback', [
            'guest_name' => 'ራሄል ተስፋ',
            'title' => 'የቀጠሮ መረጃው ጠቃሚ ነው',
            'content' => 'የጉዳዬን ቀጠሮ ቁጥሩን በመጻፍ አገኘሁት።',
        ])->assertRedirect(route('community.index', ['tab' => 'feedback']));

        $this->assertDatabaseHas('community_posts', [
            'type' => 'feedback',
            'guest_name' => 'ራሄል ተስፋ',
            'title' => 'የቀጠሮ መረጃው ጠቃሚ ነው',
            'is_published' => 1,
        ]);

        $this->get('/community?tab=feedback')
            ->assertOk()
            ->assertSee('የቀጠሮ መረጃው ጠቃሚ ነው')
            ->assertSee('አስተያየትዎ በተሳካ ሁኔታ ተልኳል።');

        $this->post('/community/feedback', ['guest_name' => '', 'title' => '', 'content' => ''])
            ->assertSessionHasErrors(['guest_name', 'title', 'content']);

        $this->assertSame(2, CommunityPost::query()->where('type', 'feedback')->count());
    }

    public function test_missing_pages_use_the_amharic_message(): void
    {
        $this->get('/የለም')->assertNotFound()->assertSee('ገጹ አልተገኘም');
    }

    public function test_role_middleware_allows_super_admin_and_blocks_inactive_users(): void
    {
        Route::middleware('role:legal_expert')->get('/testing/role-gate', fn () => 'allowed');

        $this->get('/testing/role-gate')->assertForbidden();

        $legal = User::query()->where('role', UserRole::LegalExpert)->firstOrFail();
        $this->actingAs($legal)->get('/testing/role-gate')->assertOk()->assertSee('allowed');

        $head = User::query()->where('role', UserRole::ZoneHead)->firstOrFail();
        $this->actingAs($head)->get('/testing/role-gate')->assertForbidden();

        $admin = User::query()->where('role', UserRole::SuperAdmin)->firstOrFail();
        $this->actingAs($admin)->get('/testing/role-gate')->assertOk();

        $admin->forceFill(['is_active' => false])->save();
        $this->actingAs($admin->fresh())->get('/testing/role-gate')->assertForbidden();

        $legal->forceFill(['is_active' => false])->save();
        $this->actingAs($legal->fresh())->get('/testing/role-gate')->assertForbidden();
    }
}
