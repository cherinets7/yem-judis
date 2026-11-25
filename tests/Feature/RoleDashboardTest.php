<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_each_role_gets_its_own_dashboard_title_and_shortcuts(): void
    {
        $cases = [
            'super.admin@yemjustice.test' => 'የሲስተም ዳሽቦርድ',
            'zone.head@yemjustice.test' => 'የዞን ኃላፊ ዳሽቦርድ',
            'office.head@yemjustice.test' => 'የጽህፈት ቤት ኃላፊ ዳሽቦርድ',
            'zone.plan@yemjustice.test' => 'የዕቅድ ባለሙያ ዳሽቦርድ',
            'office.plan@yemjustice.test' => 'የዕቅድ ባለሙያ ዳሽቦርድ',
            'legal.expert@yemjustice.test' => 'የህግ ባለሙያ ዳሽቦርድ',
            'hr.expert@yemjustice.test' => 'የሰው ሀብት ዳሽቦርድ',
            'unit.coordinator@yemjustice.test' => 'የክፍል አስተባባሪ ዳሽቦርድ',
        ];

        foreach ($cases as $email => $title) {
            $user = User::query()->where('email', $email)->firstOrFail();

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertSee($title)
                ->assertSee($user->role->label());
        }
    }
}
