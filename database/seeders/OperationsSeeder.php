<?php

namespace Database\Seeders;

use App\Enums\AppellantType;
use App\Enums\CourtLevel;
use App\Enums\Gender;
use App\Enums\PlanDocumentType;
use App\Enums\ProsecutorReportType;
use App\Enums\Quarter;
use App\Enums\ReviewStatus;
use App\Enums\StaffCategory;
use App\Helpers\EthiopianDateHelper;
use App\Models\AnnualPlan;
use App\Models\Attorney;
use App\Models\CivicAssociation;
use App\Models\CivilAppeal;
use App\Models\CivilCase;
use App\Models\CivilExecution;
use App\Models\Contract;
use App\Models\CriminalAppeal;
use App\Models\CriminalCase;
use App\Models\LegalSecretary;
use App\Models\LegalStudiesDissemination;
use App\Models\Organization;
use App\Models\PowerOfAttorneyGrant;
use App\Models\PowerOfAttorneyRevocation;
use App\Models\ProsecutorWeeklyReport;
use App\Models\StaffProfile;
use App\Models\StrategicFocusData;
use App\Models\User;
use App\Models\WeeklyReport;
use App\Models\WorkUnit;
use App\Support\ProsecutorWeeklyMatrix;
use Illuminate\Database\Seeder;

/**
 * Sample staff, plans, registers 001–014, and prosecutor matrices so the
 * staff workspace opens with real Amharic records instead of empty lists.
 * Toba is left without the current year's reports so the dashboard can
 * still show an office that has not submitted.
 */
class OperationsSeeder extends Seeder
{
    public function run(): void
    {
        $year = EthiopianDateHelper::currentBudgetYear();
        $zone = Organization::query()->where('type', 'zone')->firstOrFail();
        $saja = Organization::query()->where('name', 'like', '%ሳጃ ከተማ%')->firstOrFail();
        $fofa = Organization::query()->where('name', 'like', '%ፎፋ%')->firstOrFail();
        $deri = Organization::query()->where('name', 'like', '%ደሪ%')->firstOrFail();
        $toba = Organization::query()->where('name', 'like', '%ቶባ%')->firstOrFail();

        $zoneHead = User::query()->where('email', 'zone.head@yemjustice.test')->firstOrFail();
        $zonePlan = User::query()->where('email', 'zone.plan@yemjustice.test')->firstOrFail();
        $officePlan = User::query()->where('email', 'office.plan@yemjustice.test')->firstOrFail();
        $hr = User::query()->where('email', 'hr.expert@yemjustice.test')->firstOrFail();
        $legal = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();

        $this->seedStaff($hr, [
            [$zone, StaffCategory::Prosecutor, 'ዮሴፍ አበራ', 'ዐቃቤ ህግ', 'ዐቃቤ'],
            [$zone, StaffCategory::Prosecutor, 'ብርሃኔ ኃይሌ', 'ረዳት ዐቃቤ ህግ', 'ዐቃቤ'],
            [$zone, StaffCategory::PublicServant, 'ሰላም ተክሉ', 'የሰው ሀብት ኦፊሰር', 'ሰው'],
            [$zone, StaffCategory::PublicServant, 'ሕይወት በላይ', 'የእቅድ ባለሙያ', 'ዕቅድ'],
            [$saja, StaffCategory::Prosecutor, 'ሙሉጌታ ተፈራ', 'ዐቃቤ ህግ', 'ክርክር'],
            [$saja, StaffCategory::PublicServant, 'ማርታ ገበየሁ', 'ሰነድ መዝጋቢ', 'ሰነድ'],
            [$fofa, StaffCategory::Prosecutor, 'ታሪኩ ሻሎ', 'ዐቃቤ ህግ', 'ክርክር'],
            [$fofa, StaffCategory::PublicServant, 'ፋጡማ ሙሳ', 'ጽህፈት ቤት ጸሐፊ', 'ዕቅድ'],
            [$deri, StaffCategory::Prosecutor, 'አብርሃም ዳኛ', 'ዐቃቤ ህግ', 'ክርክር'],
            [$deri, StaffCategory::PublicServant, 'ሜሮን አለማየሁ', 'መዝገብ ተቆጣጣሪ', 'ሰነድ'],
            [$toba, StaffCategory::Prosecutor, 'ዳዊት ጌታቸው', 'ዐቃቤ ህግ', 'ክርክር'],
            [$toba, StaffCategory::PublicServant, 'ሄለን በቀለ', 'ገንዘብ ተቆጣጣሪ', 'ዕቅድ'],
        ]);

        $this->plan($saja, $officePlan, $zoneHead, $year, ReviewStatus::Approved, 'የሳጃ ከተማ የ'.$year.' ዓመታዊ እቅድ');
        $this->plan($zone, $zonePlan, $zoneHead, $year, ReviewStatus::Approved, 'የዞኑ የ'.$year.' ዓመታዊ እቅድ');
        $this->plan($fofa, $zonePlan, null, $year, ReviewStatus::Submitted, 'የፎፋ ወረዳ የ'.$year.' ዓመታዊ እቅድ');
        $this->plan($deri, $zonePlan, null, $year, ReviewStatus::Draft, 'የደሪ ሳጃ ዙሪያ የ'.$year.' ዓመታዊ እቅድ');

        $this->week($saja, $officePlan, $year, ReviewStatus::Submitted, 'የሳጃ ሰነድ ምዝገባና የጉዳይ ክትትል');
        $this->week($zone, $zonePlan, $year, ReviewStatus::Approved, 'የዞን ጽህፈት ቤቶች ክትትል');
        $this->week($fofa, $zonePlan, $year, ReviewStatus::Submitted, 'የፎፋ ወረዳ ሳምንታዊ ክትትል');

        $this->focus($saja, $officePlan, $year, ReviewStatus::Approved, 'የሰነድ ምዝገባ ጊዜ መቀነስ');
        $this->focus($zone, $zonePlan, $year, ReviewStatus::Submitted, 'የወረዳ ሪፖርት ሽፋን');
        $this->focus($deri, $zonePlan, $year, ReviewStatus::Draft, 'የህግ ግንዛቤ መድረኮች');

        $this->matrix($saja, $officePlan, ProsecutorReportType::DocumentRegistration, $year, 10, ReviewStatus::Draft, [
            'doc_professionals' => [
                'attorney' => ['received' => 10, 'accepted' => 6, 'rejected' => 2, 'punished' => 1],
            ],
        ]);
        $this->matrix($saja, $officePlan, ProsecutorReportType::Civil, $year, 10, ReviewStatus::Submitted, [
            'civil_plaintiff' => [
                'woreda' => ['brought_forward' => 10, 'new_intake' => 10, 'won' => 6, 'lost' => 2, 'closed' => 2, 'punished' => 3],
                'high' => ['brought_forward' => 4, 'new_intake' => 2, 'won' => 2, 'lost' => 1, 'closed' => 0, 'punished' => 1],
            ],
        ]);
        $this->matrix($saja, $officePlan, ProsecutorReportType::Criminal, $year, 10, ReviewStatus::Submitted, [
            'criminal_investigation' => [
                'person' => ['brought_forward' => 4, 'new_intake' => 6, 'charged' => 3, 'closed' => 2, 'returned' => 1],
            ],
            'criminal_detention' => [
                'police' => ['planned' => 3, 'conducted' => 3, 'seen' => 18, 'complaints' => 4, 'resolved' => 3],
                'prison' => ['planned' => 1, 'conducted' => 1, 'seen' => 22, 'complaints' => 2, 'resolved' => 1],
            ],
        ]);
        $this->matrix($fofa, $zonePlan, ProsecutorReportType::Criminal, $year, 9, ReviewStatus::Submitted, [
            'criminal_court' => [
                'woreda' => ['brought_forward' => 8, 'new_intake' => 4, 'won' => 5, 'lost' => 1, 'closed' => 1, 'punished' => 4],
            ],
        ]);

        $this->registers($saja, $legal, $year);
    }

    /**
     * @param  list<array{0: Organization, 1: StaffCategory, 2: string, 3: string, 4: string}>  $rows
     */
    private function seedStaff(User $hr, array $rows): void
    {
        foreach ($rows as [$organization, $category, $name, $title, $unitHint]) {
            StaffProfile::query()->create([
                'organization_id' => $organization->id,
                'work_unit_id' => $this->unit($organization, $unitHint)?->id,
                'staff_category' => $category,
                'full_name' => $name,
                'birth_year_ec' => 1982,
                'education_level' => 'ዲግሪ',
                'college_university' => 'ሐዋሳ ዩኒቨርሲቲ',
                'college_grad_year' => 2006,
                'job_title' => $title,
                'hire_year_ec' => 2012,
                'initial_salary' => 7200,
                'current_salary' => 12400,
                'salary_history' => [
                    ['year_ec' => 2012, 'amount' => 7200, 'note' => 'የመጀመሪያ ደመወዝ'],
                    ['year_ec' => 2017, 'amount' => 12400, 'note' => 'የደረጃ እድገት'],
                ],
                'performance_evaluations' => [
                    ['year_ec' => 2018, 'result' => 'በጣም ከፍተኛ', 'note' => 'የዓመቱ ግምገማ'],
                ],
                'promotions' => [
                    ['year_ec' => 2017, 'from_title' => 'ረዳት', 'to_title' => $title],
                ],
                'years_to_retirement' => 18,
                'created_by' => $hr->id,
                'filled_by_expert' => $hr->id,
            ]);
        }
    }

    private function plan(Organization $organization, User $author, ?User $reviewer, int $year, ReviewStatus $status, string $title): void
    {
        AnnualPlan::query()->create([
            'organization_id' => $organization->id,
            'user_id' => $author->id,
            'budget_year_ec' => $year,
            'title' => $title,
            'document_type' => PlanDocumentType::AnnualPlan,
            'summary' => 'የጉዳይ ክትትል፣ የሰነድ ምዝገባ እና የህግ ግንዛቤ ሥራ የ'.$year.' ዕቅድ።',
            'status' => $status,
            'reviewed_by' => $status === ReviewStatus::Approved ? $reviewer?->id : null,
        ]);
    }

    private function week(Organization $organization, User $author, int $year, ReviewStatus $status, string $done): void
    {
        $unit = $this->unit($organization, 'ዕቅድ');

        if ($unit === null) {
            return;
        }

        WeeklyReport::query()->create([
            'organization_id' => $organization->id,
            'work_unit_id' => $unit->id,
            'user_id' => $author->id,
            'budget_year_ec' => $year,
            'week_number' => 10,
            'week_range_ec' => 'ኅዳር 4–10',
            'planned_activities' => 'ሳምንታዊ የጉዳይና የሰነድ ሪፖርት ማሰባሰብ።',
            'accomplished_activities' => $done,
            'challenges' => 'ከሁለት ቀበሌዎች የመጣው መረጃ ዘግይቶ ደረሰ።',
            'status' => $status,
            'reviewed_by' => $status === ReviewStatus::Approved ? User::query()->where('email', 'zone.head@yemjustice.test')->value('id') : null,
        ]);
    }

    private function focus(Organization $organization, User $author, int $year, ReviewStatus $status, string $area): void
    {
        StrategicFocusData::query()->create([
            'organization_id' => $organization->id,
            'work_unit_id' => $this->unit($organization, 'ዕቅድ')?->id,
            'user_id' => $author->id,
            'budget_year_ec' => $year,
            'quarter' => Quarter::Q2,
            'focus_area' => $area,
            'target_value' => 100,
            'achieved_value' => $status === ReviewStatus::Draft ? 0 : 64,
            'narrative_summary' => $area.' በዚህ ሩብ ዓመት የተጀመረ ሥራ ነው።',
            'status' => $status,
        ]);
    }

    /**
     * @param  array<string, array<string, array<string, int>>>  $rows
     */
    private function matrix(Organization $organization, User $author, ProsecutorReportType $type, int $year, int $week, ReviewStatus $status, array $rows): void
    {
        $matrix = ProsecutorWeeklyMatrix::calculate($type->value, $rows);
        $report = ProsecutorWeeklyReport::query()->create([
            'organization_id' => $organization->id,
            'user_id' => $author->id,
            'report_type' => $type,
            'budget_year_ec' => $year,
            'week_number' => $week,
            'period_covered_ec' => 'ኅዳር 4–10',
            'status' => $status,
        ]);

        foreach ($matrix['sections'] as $section) {
            foreach ($section['rows'] as $row) {
                $report->items()->create([
                    'section_code' => $section['code'],
                    'row_label' => $row['label'],
                    'metrics' => ['row' => $row['code']] + $row['metrics'],
                ]);
            }
        }
    }

    private function registers(Organization $saja, User $legal, int $year): void
    {
        CriminalCase::query()->create([
            'organization_id' => $saja->id,
            'court_level' => CourtLevel::WoredaCourt,
            'prosecutor_file_number' => 'SZ/ወ/021/'.$year,
            'police_file_number_and_pages' => 'ሳ/ፖ/118 ገጽ 12',
            'defendant_names' => 'ገበረ መንግስቱ',
            'defendant_address' => 'ሳጃ ከተማ፣ ቀበሌ 02',
            'defendant_gender' => Gender::Male,
            'defendant_age' => 29,
            'prosecutor_charged_crime' => 'የሰውነት ጉዳት',
            'court_file_number' => 'ሳ/ወ/221/'.$year,
            'assigned_prosecutor' => 'ሙሉጌታ ተፈራ',
            'created_by' => $legal->id,
        ]);
        CriminalCase::query()->create([
            'organization_id' => $saja->id,
            'court_level' => CourtLevel::HighCourt,
            'prosecutor_file_number' => 'SZ/ከ/008/'.$year,
            'defendant_names' => 'ታደሰ ወርቁ',
            'defendant_gender' => Gender::Male,
            'defendant_age' => 41,
            'prosecutor_charged_crime' => 'የንብረት ወረራ',
            'court_file_number' => 'ከ/ወ/044/'.$year,
            'assigned_prosecutor' => 'ዮሴፍ አበራ',
            'created_by' => $legal->id,
        ]);
        CriminalAppeal::query()->create([
            'organization_id' => $saja->id,
            'prosecutor_appeal_number' => 'SZ/ይ/004/'.$year,
            'lower_court_file_number' => 'ሳ/ወ/190/2018',
            'appellant_name' => 'የሳጃ ከተማ ዐቃቤ ህግ',
            'respondent_name' => 'አበበ ከበደ',
            'address' => 'ሳጃ ከተማ',
            'age' => 36,
            'gender' => Gender::Male,
            'lower_court_decision' => 'ነጻ ተብሎ ተወስኗል',
            'appeal_filed_date_ec' => 'ጥቅምት 18 ቀን '.$year,
            'assigned_prosecutor' => 'ብርሃኔ ኃይሌ',
            'created_by' => $legal->id,
        ]);
        CivilCase::query()->create([
            'organization_id' => $saja->id,
            'court_level' => CourtLevel::HighCourt,
            'plaintiff_name' => 'የሳጃ ከተማ አስተዳደር',
            'defendant_name' => 'ከበደ ወርቁ',
            'prosecutor_case_number' => 'SZ/ፍከ/011/'.$year,
            'claim_money_amount' => 250000,
            'assigned_prosecutor' => 'ዮሴፍ አበራ',
            'court_file_number' => 'ከ/ፍ/031/'.$year,
            'created_by' => $legal->id,
        ]);
        CivilCase::query()->create([
            'organization_id' => $saja->id,
            'court_level' => CourtLevel::WoredaCourt,
            'plaintiff_name' => 'ወ/ሮ ማርታ ገበየሁ',
            'defendant_name' => 'አልማዝ ኃይሉ',
            'gender' => Gender::Female,
            'age' => 34,
            'prosecutor_case_number' => 'SZ/ፍወ/019/'.$year,
            'claim_land_size' => 0.5,
            'assigned_prosecutor' => 'ሙሉጌታ ተፈራ',
            'created_by' => $legal->id,
        ]);
        CivilAppeal::query()->create([
            'organization_id' => $saja->id,
            'appellant_type' => AppellantType::Prosecutor,
            'prosecutor_appeal_number' => 'SZ/ፍይ/002/'.$year,
            'lower_court_file_number' => 'ሳ/ፍ/070/2018',
            'appealed_court_name' => 'የየም ዞን ከፍተኛ ፍርድ ቤት',
            'appeal_money_amount' => 80000,
            'created_by' => $legal->id,
        ]);
        CivilExecution::query()->create([
            'organization_id' => $saja->id,
            'execution_plaintiff' => 'የሳጃ ከተማ አስተዳደር',
            'execution_defendant' => 'ከበደ ወርቁ',
            'prosecutor_file_number' => 'SZ/ት/006/'.$year,
            'executing_court' => 'የሳጃ ወረዳ ፍርድ ቤት',
            'requested_money_amount' => 120000,
            'recovered_asset_amount' => 45000,
            'unrecovered_asset_amount' => 75000,
            'created_by' => $legal->id,
        ]);
        PowerOfAttorneyGrant::query()->create([
            'organization_id' => $saja->id,
            'grantor_name' => 'አበበች ተስፋ',
            'grantor_address' => 'ሳጃ ከተማ፣ ቀበሌ 01',
            'grantor_age' => 52,
            'grantor_gender' => Gender::Female,
            'poa_content' => 'የመሬት ሰነድ እንዲያዘጋጅ የተሰጠ ውክልና።',
            'registration_number' => 'ውክ/008/'.$year,
            'granted_date_ec' => 'ኅዳር 2 ቀን '.$year,
            'grantee_name' => 'ዳዊት ጌታቸው',
            'grantee_age' => 30,
            'grantee_gender' => Gender::Male,
            'created_by' => $legal->id,
        ]);
        PowerOfAttorneyRevocation::query()->create([
            'organization_id' => $saja->id,
            'revoker_name' => 'አበበች ተስፋ',
            'revoker_age' => 52,
            'revoker_gender' => Gender::Female,
            'previous_poa_ref_and_date' => 'ውክ/002/2018፣ መጋቢት 12',
            'revocation_reg_number' => 'ስረ/009/'.$year,
            'revoked_date_ec' => 'ኅዳር 6 ቀን '.$year,
            'revoked_from_name' => 'ታደሰ በቀለ',
            'revoked_from_gender' => Gender::Male,
            'created_by' => $legal->id,
        ]);
        Contract::query()->create([
            'organization_id' => $saja->id,
            'giver_name' => 'ሙሉጌታ ተፈራ',
            'giver_age' => 44,
            'giver_gender' => Gender::Male,
            'receiver_name' => 'ማርታ ገበየሁ',
            'receiver_age' => 31,
            'receiver_gender' => Gender::Female,
            'contract_summary' => 'የመኖሪያ ቤት ኪራይ ውል ለሁለት ዓመት።',
            'registration_number' => 'ውል/010/'.$year,
            'registered_date_ec' => 'ኅዳር 8 ቀን '.$year,
            'created_by' => $legal->id,
        ]);
        LegalStudiesDissemination::query()->create([
            'organization_id' => $saja->id,
            'budget_year_ec' => $year,
            'needs_assessment_topic' => 'የሴቶች ንብረት መብት እና የሰነድ ምዝገባ',
            'manuals_prepared' => 1,
            'brochures_prepared' => 200,
            'awareness_platforms_count' => 4,
            'reached_kebeles_cities' => 6,
            'reached_schools' => 3,
            'legal_advice_institutions' => 5,
            'legal_advice_individuals' => 28,
            'legal_advice_total' => 33,
            'total_citizens_male' => 140,
            'total_citizens_female' => 186,
            'total_citizens_sum' => 326,
            'created_by' => $legal->id,
        ]);
        CivicAssociation::query()->create([
            'organization_id' => $saja->id,
            'association_name' => 'የሳጃ የሴቶች ማኅበር',
            'address' => 'ሳጃ ከተማ፣ ቀበሌ 03',
            'association_type' => 'በጎ አድራጎት',
            'members_male' => 4,
            'members_female' => 36,
            'members_total' => 40,
            'registration_number' => 'ማሕ/012/'.$year,
            'is_renewed_this_year' => true,
            'created_by' => $legal->id,
        ]);
        Attorney::query()->create([
            'organization_id' => $saja->id,
            'full_name' => 'ጠበቃ ሰለሞን ተክላ',
            'gender' => Gender::Male,
            'age' => 39,
            'address' => 'ሳጃ ከተማ',
            'licensing_body' => 'የክልሉ ጠበቆች ማኅበር',
            'license_number' => 'ጠ/013/'.$year,
            'licensed_date_ec' => 'መስከረም 20 ቀን '.$year,
            'is_renewed_this_year' => true,
            'service_year_ec' => $year,
            'created_by' => $legal->id,
        ]);
        LegalSecretary::query()->create([
            'organization_id' => $saja->id,
            'full_name' => 'ጸሐፊ አልማዝ ኃይሉ',
            'age' => 33,
            'gender' => Gender::Female,
            'address' => 'ሳጃ ከተማ፣ ቀበሌ 01',
            'license_number' => 'ጸ/014/'.$year,
            'licensed_date_ec' => 'ጥቅምት 5 ቀን '.$year,
            'clients_served_male' => 11,
            'clients_served_female' => 17,
            'clients_served_total' => 28,
            'is_renewed_this_year' => true,
            'created_by' => $legal->id,
        ]);
    }

    private function unit(Organization $organization, string $hint): ?WorkUnit
    {
        return WorkUnit::query()
            ->where('organization_id', $organization->id)
            ->where('name', 'like', '%'.$hint.'%')
            ->first();
    }
}
