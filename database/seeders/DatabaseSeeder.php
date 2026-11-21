<?php

namespace Database\Seeders;

use App\Enums\OrganizationType;
use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\User;
use App\Models\WorkUnit;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the Yem Zone Justice Department, its woreda and town offices,
     * their work units, and one active demo user for each role.
     *
     * Every demo account uses the password "password".
     */
    public function run(): void
    {
        $zone = Organization::query()->create([
            'name' => 'የየም ዞን ፍትህ መምሪያ (Yem Zone Justice Department)',
            'type' => OrganizationType::Zone,
            'parent_id' => null,
        ]);

        $offices = [
            'saja' => Organization::query()->create([
                'name' => 'የሳጃ ከተማ ፍትህ ጽህፈት ቤት (Saja Town Justice Office)',
                'type' => OrganizationType::City,
                'parent_id' => $zone->id,
            ]),
            'fofa' => Organization::query()->create([
                'name' => 'የፎፋ ወረዳ ፍትህ ጽህፈት ቤት (Fofa Woreda Justice Office)',
                'type' => OrganizationType::Woreda,
                'parent_id' => $zone->id,
            ]),
            'deri' => Organization::query()->create([
                'name' => 'የደሪ ሳጃ ዙሪያ ወረዳ ፍትህ ጽህፈት ቤት (Deri Saja Zuria Woreda Justice Office)',
                'type' => OrganizationType::Woreda,
                'parent_id' => $zone->id,
            ]),
            'toba' => Organization::query()->create([
                'name' => 'የቶባ ወረዳ ፍትህ ጽህፈት ቤት (Toba Woreda Justice Office)',
                'type' => OrganizationType::Woreda,
                'parent_id' => $zone->id,
            ]),
        ];

        $zoneUnits = $this->seedWorkUnits($zone, [
            'head' => 'የመምሪያ ኃላፊ ጽህፈት ቤት (Office of the Department Head)',
            'plan' => 'ዕቅድ፣ ክትትልና ግምገማ ሥራ ክፍል (Planning, Monitoring and Evaluation)',
            'hr' => 'የሰው ኃይል አስተዳደር ሥራ ክፍል (Human Resource Management)',
            'prosecution' => 'የዐቃቤ ህግ ማስተባበሪያ ሥራ ክፍል (Prosecution Coordination)',
            'documents' => 'የሰነድ ምዝገባና ውክልና ሥራ ክፍል (Document Registration and Power of Attorney)',
            'awareness' => 'የህግ ጥናትና ማኅበረሰብ ግንዛቤ ሥራ ክፍል (Legal Studies and Community Awareness)',
        ]);

        $officeUnitNames = [
            'head' => 'የጽህፈት ቤት ኃላፊ (Office of the Head)',
            'plan' => 'ዕቅድና ሪፖርት (Planning and Reporting)',
            'litigation' => 'ክርክር ክትትል (Litigation Follow-up)',
            'documents' => 'የሰነድ ምዝገባ (Document Registration)',
        ];

        $sajaUnits = $this->seedWorkUnits($offices['saja'], $officeUnitNames);
        $this->seedWorkUnits($offices['fofa'], $officeUnitNames);
        $this->seedWorkUnits($offices['deri'], $officeUnitNames);
        $this->seedWorkUnits($offices['toba'], $officeUnitNames);

        $accounts = [
            [
                'name' => 'መሮን ተስፋዬ (System Administrator)',
                'email' => 'super.admin@yemjustice.test',
                'role' => UserRole::SuperAdmin,
                'organization_id' => $zone->id,
                'work_unit_id' => null,
            ],
            [
                'name' => 'አበበች ግርማ (Zone Department Head)',
                'email' => 'zone.head@yemjustice.test',
                'role' => UserRole::ZoneHead,
                'organization_id' => $zone->id,
                'work_unit_id' => $zoneUnits['head']->id,
            ],
            [
                'name' => 'ታደሰ በቀለ (Zone Plan Expert)',
                'email' => 'zone.plan@yemjustice.test',
                'role' => UserRole::ZonePlanExpert,
                'organization_id' => $zone->id,
                'work_unit_id' => $zoneUnits['plan']->id,
            ],
            [
                'name' => 'ሃና ወርቁ (Unit Coordinator)',
                'email' => 'unit.coordinator@yemjustice.test',
                'role' => UserRole::UnitCoordinator,
                'organization_id' => $zone->id,
                'work_unit_id' => $zoneUnits['prosecution']->id,
            ],
            [
                'name' => 'ሰሎሞን ደስታ (HR Expert)',
                'email' => 'hr.expert@yemjustice.test',
                'role' => UserRole::HrExpert,
                'organization_id' => $zone->id,
                'work_unit_id' => $zoneUnits['hr']->id,
            ],
            [
                'name' => 'ፋጡማ አህመድ (Legal Expert)',
                'email' => 'legal.expert@yemjustice.test',
                'role' => UserRole::LegalExpert,
                'organization_id' => $zone->id,
                'work_unit_id' => $zoneUnits['awareness']->id,
            ],
            [
                'name' => 'ከበደ አለሙ (Saja Office Head)',
                'email' => 'office.head@yemjustice.test',
                'role' => UserRole::OfficeHead,
                'organization_id' => $offices['saja']->id,
                'work_unit_id' => $sajaUnits['head']->id,
            ],
            [
                'name' => 'ራሄል መንግስቱ (Saja Plan Expert)',
                'email' => 'office.plan@yemjustice.test',
                'role' => UserRole::OfficePlanExpert,
                'organization_id' => $offices['saja']->id,
                'work_unit_id' => $sajaUnits['plan']->id,
            ],
        ];

        foreach ($accounts as $account) {
            User::query()->create([
                ...$account,
                'password' => 'password',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        $this->call(PublicPortalSeeder::class);
        $this->call(OperationsSeeder::class);
        $this->call(PortalSettingSeeder::class);
    }

    /**
     * @param  array<string, string>  $units
     * @return array<string, WorkUnit>
     */
    private function seedWorkUnits(Organization $organization, array $units): array
    {
        $created = [];

        foreach ($units as $key => $name) {
            $created[$key] = WorkUnit::query()->create([
                'organization_id' => $organization->id,
                'name' => $name,
            ]);
        }

        return $created;
    }
}
