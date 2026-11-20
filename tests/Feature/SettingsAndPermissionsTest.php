<?php

namespace Tests\Feature;

use App\Models\RolePermission;
use App\Models\Setting;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_public_and_staff_pages_show_the_footer(): void
    {
        $note = 'የህዝብ መረጃ በር ብቻ። የጉዳይ ዝርዝር የሚታየው ከታተመ በኋላ ነው።';

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('ሳጃ፣ የየም ዞን፣ ማዕከላዊ ኢትዮጵያ')
            ->assertSee($note);

        $admin = User::query()->where('email', 'super.admin@yemjustice.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($note)
            ->assertSee('ቅንብሮች')
            ->assertSee('ፈቃዶች');
    }

    public function test_zone_head_can_save_settings_that_appear_in_the_footer(): void
    {
        $head = User::query()->where('email', 'zone.head@yemjustice.test')->firstOrFail();
        $planner = User::query()->where('email', 'office.plan@yemjustice.test')->firstOrFail();

        $this->actingAs($planner)->get(route('settings.edit'))
            ->assertOk()
            ->assertSee('የማስተካከል ፈቃድ የለዎትም')
            ->assertDontSee('አስቀምጥ');

        $this->actingAs($planner)->put(route('settings.update'), [
            'department_name' => 'የተቀየረ መምሪያ',
            'address' => 'አዲስ አድራሻ',
            'phone' => '0460000000',
            'email' => 'office@yemjustice.test',
            'footer_note' => 'አዲስ ማስታወሻ',
        ])->assertForbidden();

        $this->actingAs($head)->put(route('settings.update'), [
            'department_name' => 'የየም ዞን ፍትህ መምሪያ',
            'address' => 'ሳጃ፣ የየም ዞን፣ ማዕከላዊ ኢትዮጵያ',
            'phone' => '0461234567',
            'email' => 'info@yemjustice.test',
            'footer_note' => 'የህዝብ መረጃ በር ብቻ። የጉዳይ ዝርዝር የሚታየው ከታተመ በኋላ ነው።',
        ])->assertRedirect(route('settings.edit'));

        $this->assertSame('0461234567', Setting::current()->phone);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('0461234567')
            ->assertSee('info@yemjustice.test');
    }

    public function test_only_a_permitted_role_can_edit_the_permission_matrix(): void
    {
        $admin = User::query()->where('email', 'super.admin@yemjustice.test')->firstOrFail();
        $legal = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();

        $this->actingAs($legal)->get(route('permissions.edit'))->assertForbidden();
        $this->actingAs($legal)->get(route('admin.laws.index'))->assertOk();

        $granted = [];

        foreach (array_keys(PermissionCatalog::groups()) as $group) {
            $roles = PermissionCatalog::roles($group);

            if ($group === 'laws') {
                $roles = array_values(array_filter(
                    $roles,
                    fn (string $role): bool => $role !== 'legal_expert',
                ));
            }

            $granted[$group] = $roles;
        }

        $this->actingAs($admin)
            ->put(route('permissions.update'), ['granted' => $granted])
            ->assertRedirect(route('permissions.edit'));

        $this->assertFalse(
            RolePermission::query()
                ->where('group', 'laws')
                ->where('role', 'legal_expert')
                ->value('allowed')
        );

        $this->actingAs($legal)->get(route('admin.laws.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.laws.index'))->assertOk();
    }
}
