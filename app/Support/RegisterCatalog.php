<?php

namespace App\Support;

use App\Enums\AppellantType;
use App\Enums\CommunityPostType;
use App\Enums\CourtLevel;
use App\Enums\FinalOutcome;
use App\Enums\Gender;
use App\Enums\LawCategory;
use App\Enums\LawJurisdiction;
use App\Enums\PlanDocumentType;
use App\Enums\PublicCaseType;
use App\Enums\Quarter;
use App\Enums\ReviewStatus;
use App\Enums\StaffCategory;
use App\Http\Controllers\AnnualPlanController;
use App\Http\Controllers\AttorneyController;
use App\Http\Controllers\CivicAssociationController;
use App\Http\Controllers\CivilAppealController;
use App\Http\Controllers\CivilCaseController;
use App\Http\Controllers\CivilExecutionController;
use App\Http\Controllers\CommunityPostController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\CriminalAppealController;
use App\Http\Controllers\CriminalCaseController;
use App\Http\Controllers\LawController;
use App\Http\Controllers\LegalSecretaryController;
use App\Http\Controllers\LegalStudyController;
use App\Http\Controllers\PowerOfAttorneyGrantController;
use App\Http\Controllers\PowerOfAttorneyRevocationController;
use App\Http\Controllers\PublicCaseTrackingController;
use App\Http\Controllers\StaffProfileController;
use App\Http\Controllers\StrategicFocusController;
use App\Http\Controllers\WeeklyReportController;
use App\Models\AnnualPlan;
use App\Models\Attorney;
use App\Models\CivicAssociation;
use App\Models\CivilAppeal;
use App\Models\CivilCase;
use App\Models\CivilExecution;
use App\Models\CommunityPost;
use App\Models\Contract;
use App\Models\CriminalAppeal;
use App\Models\CriminalCase;
use App\Models\Law;
use App\Models\LegalSecretary;
use App\Models\LegalStudiesDissemination;
use App\Models\PowerOfAttorneyGrant;
use App\Models\PowerOfAttorneyRevocation;
use App\Models\PublicCaseTracking;
use App\Models\StaffProfile;
use App\Models\StrategicFocusData;
use App\Models\WeeklyReport;
use InvalidArgumentException;

class RegisterCatalog
{
    /**
     * @var array<string, array<string, mixed>>|null
     */
    private static ?array $cache = null;

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::$cache ??= self::definitions();
    }

    /**
     * @return array<string, mixed>
     */
    public static function get(string $key): array
    {
        $all = self::all();

        if (! isset($all[$key])) {
            throw new InvalidArgumentException('Unknown register ['.$key.'].');
        }

        return $all[$key];
    }

    /**
     * @return list<array{heading: string, roles: string, group: string, keys: list<string>}>
     */
    public static function sections(): array
    {
        return [
            [
                'heading' => 'ዓመታዊ/ሳምንታዊ/ስትራቴጂክ ሪፖርቶች',
                'roles' => Roles::REPORTS,
                'group' => 'reports',
                'keys' => ['annual-plans', 'weekly-reports', 'strategic-focus'],
            ],
            [
                'heading' => 'የሰው ሀብት',
                'roles' => Roles::HR,
                'group' => 'hr',
                'keys' => ['staff-profiles'],
            ],
            [
                'heading' => 'ጉዳዮች 001–007',
                'roles' => Roles::LEGAL,
                'group' => 'legal',
                'keys' => ['criminal-cases', 'criminal-appeals', 'civil-cases', 'civil-appeals', 'civil-executions'],
            ],
            [
                'heading' => 'ውክልና/ውሎች/ጥብቅና 008–014',
                'roles' => Roles::LEGAL,
                'group' => 'legal',
                'keys' => ['poa-grants', 'poa-revocations', 'contracts', 'legal-studies', 'civic-associations', 'attorneys', 'legal-secretaries'],
            ],
            [
                'heading' => 'ህጎች',
                'roles' => Roles::LAWS,
                'group' => 'laws',
                'keys' => ['laws'],
            ],
            [
                'heading' => 'ሌሎች/አስተያየቶች',
                'roles' => Roles::COMMUNITY,
                'group' => 'community',
                'keys' => ['community-posts'],
            ],
            [
                'heading' => 'የፐብሊክ ጉዳይ መከታተያ',
                'roles' => Roles::TRACKING,
                'group' => 'tracking',
                'keys' => ['case-tracking'],
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function definitions(): array
    {
        $registers = [
            self::entry('annual-plans', 'ዓመታዊ እቅድና ሪፖርት', 'ዓመታዊ', AnnualPlanController::class, AnnualPlan::class, [
                'review' => true,
                'reviewer' => true,
                'note' => true,
                'search' => ['title', 'summary'],
                'columns' => ['title', 'budget_year_ec', 'document_type', 'status'],
                'fields' => [
                    self::year('budget_year_ec', 'የበጀት ዓመት', true),
                    self::f('title', 'ርዕስ', 'string', true),
                    self::f('document_type', 'የሰነድ አይነት', 'enum', true, ['enum' => PlanDocumentType::class]),
                    self::status(),
                    self::text('summary', 'ማጠቃለያ'),
                    self::f('file_path', 'ፋይል', 'file', false, ['directory' => 'annual-plans', 'span' => 'full']),
                ],
            ]),
            self::entry('weekly-reports', 'ሳምንታዊ ሪፖርት', 'ሳምንታዊ', WeeklyReportController::class, WeeklyReport::class, [
                'review' => true,
                'reviewer' => true,
                'unique' => ['organization_id', 'work_unit_id', 'budget_year_ec', 'week_number'],
                'search' => ['planned_activities', 'accomplished_activities', 'challenges', 'week_range_ec'],
                'columns' => ['week_number', 'budget_year_ec', 'week_range_ec', 'status'],
                'fields' => [
                    self::unit(true),
                    self::year('budget_year_ec', 'የበጀት ዓመት', true),
                    self::f('week_number', 'ሳምንት', 'integer', true, ['min' => 1, 'max' => 53]),
                    self::f('week_range_ec', 'የሳምንቱ ክልል', 'string'),
                    self::status(),
                    self::text('planned_activities', 'ታቅዶ የነበረ'),
                    self::text('accomplished_activities', 'የተከናወነ'),
                    self::text('challenges', 'ፈተናዎች'),
                    self::f('attachment_path', 'አባሪ', 'file', false, ['directory' => 'weekly-reports', 'span' => 'full']),
                ],
            ]),
            self::entry('strategic-focus', 'ስትራቴጂክ ትኩረት', 'ስትራቴጂክ', StrategicFocusController::class, StrategicFocusData::class, [
                'review' => true,
                'search' => ['focus_area', 'narrative_summary'],
                'columns' => ['focus_area', 'quarter', 'budget_year_ec', 'status'],
                'fields' => [
                    self::unit(),
                    self::year('budget_year_ec', 'የበጀት ዓመት', true),
                    self::f('quarter', 'ሩብ ዓመት', 'enum', true, ['enum' => Quarter::class]),
                    self::f('focus_area', 'የትኩረት መስክ', 'string', true),
                    self::status(),
                    self::money('target_value', 'ኢላማ'),
                    self::money('achieved_value', 'የተገኘ'),
                    self::text('narrative_summary', 'የጽሑፍ ማጠቃለያ'),
                ],
            ]),
            self::entry('staff-profiles', 'የሰው ሀብት', 'የሰው ሀብት', StaffProfileController::class, StaffProfile::class, [
                'search' => ['full_name', 'job_title', 'position_id_number', 'pension_id_number'],
                'columns' => ['full_name', 'staff_category', 'job_title', 'current_salary'],
                'fields' => [
                    self::unit(),
                    self::f('staff_category', 'የሰራተኛ መደብ', 'enum', true, ['enum' => StaffCategory::class]),
                    self::f('full_name', 'ሙሉ ስም', 'string', true),
                    self::year('birth_year_ec', 'የትውልድ ዓመት'),
                    self::text('schools_attended', 'የተማሩባቸው ትምህርት ቤቶች'),
                    self::f('primary_school_and_year', 'የመጀመሪያ ደረጃ ትምህርት ቤትና ዓመት', 'string'),
                    self::f('secondary_school_and_year', 'ሁለተኛ ደረጃ ትምህርት ቤትና ዓመት', 'string'),
                    self::f('college_university', 'ኮሌጅ/ዩኒቨርሲቲ', 'string'),
                    self::year('college_grad_year', 'የምረቃ ዓመት'),
                    self::f('education_level', 'የትምህርት ደረጃ', 'string'),
                    self::f('profession_track', 'የሙያ መስመር', 'string'),
                    self::f('higher_ed_gpa', 'የከፍተኛ ትምህርት ነጥብ', 'decimal', false, ['min' => 0, 'max' => 99.99]),
                    self::f('position_id_number', 'የቦታ መለያ', 'string'),
                    self::f('job_title', 'የሥራ መደብ', 'string'),
                    self::f('pension_id_number', 'የጡረታ መለያ', 'string'),
                    self::year('hire_year_ec', 'የቅጥር ዓመት'),
                    self::money('initial_salary', 'የመጀመሪያ ደመወዝ'),
                    self::money('current_salary', 'የአሁን ደመወዝ'),
                    self::rows('salary_history', 'የደመወዝ ታሪክ', [
                        ['name' => 'year_ec', 'label' => 'ዓመት', 'type' => 'integer'],
                        ['name' => 'amount', 'label' => 'መጠን', 'type' => 'decimal'],
                        ['name' => 'note', 'label' => 'ማስታወሻ', 'type' => 'string'],
                    ]),
                    self::rows('performance_evaluations', 'የአፈጻጸም ግምገማ', [
                        ['name' => 'year_ec', 'label' => 'ዓመት', 'type' => 'integer'],
                        ['name' => 'result', 'label' => 'ውጤት', 'type' => 'string'],
                        ['name' => 'note', 'label' => 'ማስታወሻ', 'type' => 'string'],
                    ]),
                    self::rows('promotions', 'ዕድገት', [
                        ['name' => 'year_ec', 'label' => 'ዓመት', 'type' => 'integer'],
                        ['name' => 'from_title', 'label' => 'ከመደብ', 'type' => 'string'],
                        ['name' => 'to_title', 'label' => 'ወደ መደብ', 'type' => 'string'],
                    ]),
                    self::f('guarantor_name', 'ዋስ', 'string'),
                    self::f('years_to_retirement', 'እስከ ጡረታ የቀረው ዓመት', 'integer', false, ['min' => 0, 'max' => 80]),
                    self::expert('filled_by_expert', 'የሞላው ባለሙያ'),
                ],
            ]),
            self::entry('laws', 'ህጎች', 'ህጎች', LawController::class, Law::class, [
                'scoped' => false,
                'publish' => true,
                'search' => ['title', 'law_number', 'description'],
                'columns' => ['title', 'jurisdiction', 'category', 'is_published'],
                'fields' => [
                    self::f('jurisdiction', 'ስልጣን', 'enum', true, ['enum' => LawJurisdiction::class]),
                    self::f('category', 'ምድብ', 'enum', true, ['enum' => LawCategory::class]),
                    self::f('title', 'ርዕስ', 'string', true),
                    self::f('law_number', 'ቁጥር', 'string'),
                    self::year('issued_year_ec', 'የወጣበት ዓመት'),
                    self::flag('is_published', 'ለህዝብ ታትሟል'),
                    self::text('description', 'መግለጫ'),
                    self::f('file_path', 'ፋይል', 'file', false, ['directory' => 'laws', 'span' => 'full']),
                ],
            ]),
            self::entry('community-posts', 'ሌሎች እና አስተያየቶች', 'ሌሎች/አስተያየቶች', CommunityPostController::class, CommunityPost::class, [
                'publish' => true,
                'nullable_org' => true,
                'search' => ['title', 'content', 'guest_name'],
                'columns' => ['title', 'type', 'guest_name', 'is_published'],
                'fields' => [
                    self::f('type', 'አይነት', 'enum', true, ['enum' => CommunityPostType::class]),
                    self::f('title', 'ርዕስ', 'string', true),
                    self::f('guest_name', 'የእንግዳ ስም', 'string'),
                    self::flag('is_published', 'ለህዝብ ታትሟል'),
                    self::text('content', 'ይዘት', true),
                ],
            ]),
            self::entry('case-tracking', 'የፐብሊክ ጉዳይ መከታተያ', 'የፐብሊክ ጉዳይ መከታተያ', PublicCaseTrackingController::class, PublicCaseTracking::class, [
                'publish' => true,
                'search' => ['prosecutor_file_number', 'court_file_number', 'plaintiff_name', 'defendant_name'],
                'columns' => ['prosecutor_file_number', 'case_type', 'plaintiff_name', 'expert_id', 'is_published'],
                'fields' => [
                    self::f('prosecutor_file_number', 'የዐቃቤ ህግ መዝገብ ቁጥር', 'string', true),
                    self::f('court_file_number', 'የፍርድ ቤት መዝገብ ቁጥር', 'string'),
                    self::f('plaintiff_name', 'ከሳሽ', 'string'),
                    self::f('defendant_name', 'ተከሳሽ', 'string'),
                    self::f('case_type', 'የጉዳይ አይነት', 'enum', true, ['enum' => PublicCaseType::class]),
                    self::f('court_level', 'የፍርድ ቤት ደረጃ', 'enum', true, ['enum' => CourtLevel::class]),
                    self::expert('expert_id', 'የተሳሰረ ባለሙያ'),
                    self::f('appointment_date_ec', 'የቀጠሮ ቀን', 'string'),
                    self::f('final_outcome', 'የመጨረሻ ውጤት', 'enum', true, [
                        'enum' => FinalOutcome::class,
                        'default' => FinalOutcome::Pending->value,
                    ]),
                    self::flag('is_published', 'ለህዝብ ታትሟል'),
                    self::text('appointment_reason', 'የቀጠሮ ምክንያት'),
                ],
            ]),
            self::entry('criminal-cases', 'የወንጀል ጉዳይ', '001/002 ወንጀል', CriminalCaseController::class, CriminalCase::class, [
                'code' => '001–002',
                'publish' => true,
                'project_public' => true,
                'documents' => true,
                'search' => ['prosecutor_file_number', 'defendant_names', 'court_file_number', 'assigned_prosecutor'],
                'columns' => ['prosecutor_file_number', 'court_level', 'defendant_names', 'expert_id'],
                'fields' => [
                    self::f('court_level', 'የፍርድ ቤት ደረጃ', 'enum', true, ['enum' => CourtLevel::class]),
                    self::f('prosecutor_file_number', 'የዐቃቤ ህግ መዝገብ ቁጥር', 'string', true),
                    self::f('police_file_number_and_pages', 'የፖሊስ መዝገብ ቁጥርና ገጽ', 'string'),
                    self::f('complainant_and_police_date', 'ወንጀል ተቤዥ እና የፖሊስ ቀን', 'string'),
                    self::f('submitted_to_justice_date', 'ለፍትህ የቀረበበት ቀን', 'string'),
                    self::f('defendant_gender', 'ጾታ', 'enum', false, ['enum' => Gender::class]),
                    self::age('defendant_age', 'እድሜ'),
                    self::f('victim_and_age', 'ተጎጂ እና እድሜ', 'string'),
                    self::f('filed_in_court_date', 'ለፍርድ ቤት የቀረበበት ቀን', 'string'),
                    self::f('court_file_number', 'የፍርድ ቤት መዝገብ ቁጥር', 'string'),
                    self::f('court_decision_type', 'የውሳኔ አይነት', 'string'),
                    self::f('case_age', 'የጉዳዩ ዕድሜ', 'string'),
                    self::f('assigned_prosecutor', 'የተመደበ ዐቃቤ ህግ', 'string'),
                    self::expert('expert_id', 'የተሳሰረ ባለሙያ'),
                    self::f('appointment_date_ec', 'የቀጠሮ ቀን', 'string'),
                    self::text('appointment_reason', 'የቀጠሮ ምክንያት'),
                    self::f('witnesses_count_and_gender', 'ምስክሮች ብዛትና ጾታ', 'string'),
                    self::flag('closed_indefinitely', 'ላልተወሰነ ጊዜ ተዘግቷል'),
                    self::text('defendant_names', 'የተከሳሾች ስም'),
                    self::text('defendant_address', 'አድራሻ'),
                    self::text('police_investigated_crime', 'ፖሊስ የመረመረው ወንጀል'),
                    self::text('prosecutor_charged_crime', 'ዐቃቤ ህግ የከሰሰው ወንጀል'),
                ],
            ]),
            self::entry('criminal-appeals', 'የወንጀል ይግባኝ', '003 ይግባኝ', CriminalAppealController::class, CriminalAppeal::class, [
                'code' => '003',
                'publish' => true,
                'project_public' => true,
                'documents' => true,
                'search' => ['prosecutor_appeal_number', 'appellant_name', 'respondent_name', 'lower_court_file_number'],
                'columns' => ['prosecutor_appeal_number', 'appellant_name', 'respondent_name', 'expert_id'],
                'fields' => [
                    self::f('prosecutor_appeal_number', 'የይግባኝ ቁጥር', 'string', true),
                    self::f('lower_court_file_number', 'የታችኛው ፍርድ ቤት መዝገብ', 'string'),
                    self::f('appellant_name', 'ይግባኝ ባይ', 'string'),
                    self::f('respondent_name', 'ተጠሪ', 'string'),
                    self::age('age', 'እድሜ'),
                    self::gender('gender', 'ጾታ'),
                    self::f('appeal_filed_date_ec', 'ይግባኝ የቀረበበት ቀን', 'string'),
                    self::f('appellate_court_and_file_no', 'የይግባኝ ፍርድ ቤትና መዝገብ', 'string'),
                    self::f('assigned_prosecutor', 'የተመደበ ዐቃቤ ህግ', 'string'),
                    self::expert('expert_id', 'የተሳሰረ ባለሙያ'),
                    self::f('appointment_date_ec', 'የቀጠሮ ቀን', 'string'),
                    self::text('appointment_reason', 'የቀጠሮ ምክንያት'),
                    self::f('case_age', 'የጉዳዩ ዕድሜ', 'string'),
                    self::text('address', 'አድራሻ'),
                    self::text('lower_court_decision', 'የታችኛው ፍርድ ቤት ውሳኔ'),
                    self::text('decision_date_and_outcome', 'የውሳኔ ቀንና ውጤት'),
                ],
            ]),
            self::entry('civil-cases', 'የፍትሐ ብሔር ጉዳይ', '004/005 ፍትሐ ብሔር', CivilCaseController::class, CivilCase::class, [
                'code' => '004–005',
                'publish' => true,
                'project_public' => true,
                'documents' => true,
                'search' => ['plaintiff_name', 'defendant_name', 'prosecutor_case_number', 'court_file_number'],
                'columns' => ['plaintiff_name', 'defendant_name', 'court_level', 'expert_id'],
                'fields' => [
                    self::f('court_level', 'የፍርድ ቤት ደረጃ', 'enum', true, ['enum' => CourtLevel::class]),
                    self::f('plaintiff_name', 'ከሳሽ', 'string', true),
                    self::f('defendant_name', 'ተከሳሽ', 'string'),
                    self::gender('gender', 'ጾታ'),
                    self::age('age', 'እድሜ'),
                    self::f('prosecutor_case_number', 'የዐቃቤ ህግ መዝገብ ቁጥር', 'string'),
                    self::f('filed_date_ec', 'የቀረበበት ቀን', 'string'),
                    self::money('claim_money_amount', 'የገንዘብ ክርክር'),
                    self::f('claim_land_size', 'የመሬት ስፋት', 'decimal', false, ['min' => 0, 'max' => 9999999999.9999]),
                    self::f('assigned_prosecutor', 'የተመደበ ዐቃቤ ህግ', 'string'),
                    self::expert('expert_id', 'የተሳሰረ ባለሙያ'),
                    self::f('appointment_date_ec', 'የቀጠሮ ቀን', 'string'),
                    self::text('appointment_reason', 'የቀጠሮ ምክንያት'),
                    self::f('court_file_number', 'የፍርድ ቤት መዝገብ ቁጥር', 'string'),
                    self::f('case_age', 'የጉዳዩ ዕድሜ', 'string'),
                    self::count('witness_male', 'ወንድ ምስክር'),
                    self::count('witness_female', 'ሴት ምስክር'),
                    self::count('witness_total', 'ምስክር ድምር'),
                    self::text('plaintiff_address', 'የከሳሽ አድራሻ'),
                    self::text('defendant_address', 'የተከሳሽ አድራሻ'),
                    self::text('negotiated_recovered_asset', 'በድርድር የተመለሰ ንብረት'),
                    self::text('decision_date_and_type', 'የውሳኔ ቀንና አይነት'),
                ],
            ]),
            self::entry('civil-appeals', 'የፍትሐ ብሔር ይግባኝ', '006 ይግባኝ', CivilAppealController::class, CivilAppeal::class, [
                'code' => '006',
                'publish' => true,
                'project_public' => true,
                'documents' => true,
                'search' => ['prosecutor_appeal_number', 'appellant_name', 'respondent_name', 'appellant_address', 'respondent_address', 'lower_court_file_number'],
                'columns' => ['prosecutor_appeal_number', 'appellant_name', 'respondent_name', 'expert_id'],
                'fields' => [
                    self::f('appellant_type', 'የይግባኝ ባይ አይነት', 'enum', true, ['enum' => AppellantType::class]),
                    self::f('appellant_name', 'ይግባኝ ባይ ስም', 'string', true),
                    self::f('respondent_name', 'ተጠሪ ስም', 'string'),
                    self::f('prosecutor_appeal_number', 'የይግባኝ ቁጥር', 'string', true),
                    self::age('age', 'እድሜ'),
                    self::f('appeal_filed_date_ec', 'ይግባኝ የቀረበበት ቀን', 'string'),
                    self::f('lower_court_file_number', 'የታችኛው ፍርድ ቤት መዝገብ', 'string'),
                    self::f('appealed_court_name', 'ይግባኝ የቀረበበት ፍርድ ቤት', 'string'),
                    self::f('appellate_court_file_number', 'የይግባኝ መዝገብ ቁጥር', 'string'),
                    self::expert('expert_id', 'የተሳሰረ ባለሙያ'),
                    self::f('appointment_date_ec', 'የቀጠሮ ቀን', 'string'),
                    self::text('appointment_reason', 'የቀጠሮ ምክንያት'),
                    self::money('appeal_money_amount', 'የይግባኝ ገንዘብ'),
                    self::f('appeal_land_size', 'የይግባኝ መሬት ስፋት', 'decimal', false, ['min' => 0, 'max' => 9999999999.9999]),
                    self::f('case_age', 'የጉዳዩ ዕድሜ', 'string'),
                    self::text('appellant_address', 'የይግባኝ ባይ አድራሻ'),
                    self::text('respondent_address', 'የተጠሪ አድራሻ'),
                    self::text('appellate_decision_and_date', 'የይግባኝ ውሳኔና ቀን'),
                ],
            ]),
            self::entry('civil-executions', 'ፍርድ ትግበራ', '007 ትግበራ', CivilExecutionController::class, CivilExecution::class, [
                'code' => '007',
                'publish' => true,
                'project_public' => true,
                'documents' => true,
                'search' => ['prosecutor_file_number', 'execution_plaintiff', 'execution_defendant', 'court_file_number'],
                'columns' => ['prosecutor_file_number', 'execution_plaintiff', 'execution_defendant', 'expert_id'],
                'fields' => [
                    self::f('prosecutor_file_number', 'የዐቃቤ ህግ መዝገብ ቁጥር', 'string', true),
                    self::f('execution_plaintiff', 'አፈጻጸም ጠያቂ', 'string'),
                    self::f('execution_defendant', 'አፈጻጸም ተጠያቂ', 'string'),
                    self::age('age', 'እድሜ'),
                    self::gender('gender', 'ጾታ'),
                    self::f('executing_court', 'አፈጻጸም የሚከታተለው ፍርድ ቤት', 'string'),
                    self::f('court_file_number', 'የፍርድ ቤት መዝገብ ቁጥር', 'string'),
                    self::expert('expert_id', 'የተሳሰረ ባለሙያ'),
                    self::f('appointment_date_ec', 'የቀጠሮ ቀን', 'string'),
                    self::text('appointment_reason', 'የቀጠሮ ምክንያት'),
                    self::money('requested_money_amount', 'የተጠየቀ ገንዘብ'),
                    self::f('requested_land_size', 'የተጠየቀ መሬት', 'decimal', false, ['min' => 0, 'max' => 9999999999.9999]),
                    self::money('recovered_asset_amount', 'የተመለሰ ንብረት'),
                    self::money('unrecovered_asset_amount', 'ያልተመለሰ ንብረት'),
                    self::text('plaintiff_address', 'የጠያቂ አድራሻ'),
                    self::text('defendant_address', 'የተጠያቂ አድራሻ'),
                    self::text('court_decision_date_and_summary', 'የውሳኔ ቀንና ማጠቃለያ'),
                ],
            ]),
            self::entry('poa-grants', 'ውክልና', '008 ውክልና', PowerOfAttorneyGrantController::class, PowerOfAttorneyGrant::class, [
                'code' => '008',
                'documents' => true,
                'search' => ['grantor_name', 'grantee_name', 'registration_number'],
                'columns' => ['registration_number', 'grantor_name', 'grantee_name', 'granted_date_ec'],
                'fields' => self::partyPair('grantor', 'ውክልና ሰጪ', 'grantee', 'ውክልና ተቀባይ', [
                    self::text('poa_content', 'የውክልና ይዘት'),
                    self::f('registration_number', 'የምዝገባ ቁጥር', 'string', true),
                    self::f('granted_date_ec', 'የተሰጠበት ቀን', 'string'),
                    self::money('stamp_fee', 'የማህተም ክፍያ'),
                    self::money('service_fee', 'የአገልግሎት ክፍያ'),
                    self::expert('approved_by_expert', 'ያጸደቀው ባለሙያ'),
                ]),
            ]),
            self::entry('poa-revocations', 'የውክልና ስረዛ', '009 ስረዛ', PowerOfAttorneyRevocationController::class, PowerOfAttorneyRevocation::class, [
                'code' => '009',
                'documents' => true,
                'search' => ['revoker_name', 'revoked_from_name', 'revocation_reg_number', 'previous_poa_ref_and_date'],
                'columns' => ['revocation_reg_number', 'revoker_name', 'revoked_from_name', 'revoked_date_ec'],
                'fields' => self::partyPair('revoker', 'ሻሪ', 'revoked_from', 'የተሰረዘበት', [
                    self::f('previous_poa_ref_and_date', 'የቀድሞ ውክልና ቁጥርና ቀን', 'string'),
                    self::f('revocation_reg_number', 'የስረዛ ምዝገባ ቁጥር', 'string', true),
                    self::f('revoked_date_ec', 'የተሰረዘበት ቀን', 'string'),
                    self::money('stamp_fee', 'የማህተም ክፍያ'),
                    self::money('service_fee', 'የአገልግሎት ክፍያ'),
                    self::expert('approved_by_expert', 'ያጸደቀው ባለሙያ'),
                ]),
            ]),
            self::entry('contracts', 'ውል', '010 ውል', ContractController::class, Contract::class, [
                'code' => '010',
                'documents' => true,
                'search' => ['giver_name', 'receiver_name', 'registration_number', 'contract_summary'],
                'columns' => ['registration_number', 'giver_name', 'receiver_name', 'registered_date_ec'],
                'fields' => self::partyPair('giver', 'ውል ሰጪ', 'receiver', 'ውል ተቀባይ', [
                    self::f('registration_number', 'የምዝገባ ቁጥር', 'string', true),
                    self::f('registered_date_ec', 'የተመዘገበበት ቀን', 'string'),
                    self::money('service_fee', 'የአገልግሎት ክፍያ'),
                    self::text('contract_summary', 'የውሉ ማጠቃለያ'),
                    self::expert('approved_by_expert', 'ያጸደቀው ባለሙያ'),
                ]),
            ]),
            self::entry('legal-studies', 'የህግ ጥናትና ስርጭት', '011 ጥናት', LegalStudyController::class, LegalStudiesDissemination::class, [
                'code' => '011',
                'documents' => true,
                'search' => ['needs_assessment_topic'],
                'columns' => ['budget_year_ec', 'needs_assessment_topic', 'manuals_prepared', 'drafted_laws_count'],
                'fields' => array_merge([
                    self::year('budget_year_ec', 'የበጀት ዓመት', true),
                    self::text('needs_assessment_topic', 'የፍላጎት ግምገማ ርዕስ'),
                ], self::studyCounts()),
            ]),
            self::entry('civic-associations', 'ሲቪክ ማኅበር', '012 ማኅበር', CivicAssociationController::class, CivicAssociation::class, [
                'code' => '012',
                'documents' => true,
                'search' => ['association_name', 'registration_number', 'association_type'],
                'columns' => ['association_name', 'association_type', 'registration_number', 'members_total'],
                'fields' => [
                    self::f('association_name', 'የማኅበሩ ስም', 'string', true),
                    self::f('association_type', 'የማኅበር አይነት', 'string'),
                    self::f('registration_number', 'የምዝገባ ቁጥር', 'string'),
                    self::f('certificate_issued_date_ec', 'የምስክር ወረቀት ቀን', 'string'),
                    self::count('members_male', 'ወንድ አባላት'),
                    self::count('members_female', 'ሴት አባላት'),
                    self::count('members_total', 'አባላት ድምር'),
                    self::money('capital_amount', 'ካፒታል'),
                    self::money('registration_fee', 'የምዝገባ ክፍያ'),
                    self::money('service_fee', 'የአገልግሎት ክፍያ'),
                    self::flag('is_renewed_this_year', 'በዚህ ዓመት ታድሷል'),
                    self::text('address', 'አድራሻ'),
                    self::expert('served_by_expert', 'ያገለገለው ባለሙያ'),
                ],
            ]),
            self::entry('attorneys', 'ጠበቃ', '013 ጠበቃ', AttorneyController::class, Attorney::class, [
                'code' => '013',
                'documents' => true,
                'search' => ['full_name', 'license_number', 'licensing_body'],
                'columns' => ['full_name', 'license_number', 'service_year_ec', 'is_renewed_this_year'],
                'fields' => [
                    self::f('full_name', 'ሙሉ ስም', 'string', true),
                    self::gender('gender', 'ጾታ'),
                    self::age('age', 'እድሜ'),
                    self::f('licensing_body', 'ፈቃድ የሰጠ አካል', 'string'),
                    self::f('license_number', 'የፈቃድ ቁጥር', 'string'),
                    self::f('licensed_date_ec', 'ፈቃድ የተሰጠበት ቀን', 'string'),
                    self::year('service_year_ec', 'የአገልግሎት ዓመት'),
                    self::money('license_fee', 'የፈቃድ ክፍያ'),
                    self::money('service_fee', 'የአገልግሎት ክፍያ'),
                    self::count('pro_bono_clients_male', 'ነጻ አገልግሎት ወንድ'),
                    self::count('pro_bono_clients_female', 'ነጻ አገልግሎት ሴት'),
                    self::count('pro_bono_clients_total', 'ነጻ አገልግሎት ድምር'),
                    self::count('pro_bono_hours', 'የነጻ አገልግሎት ሰዓት'),
                    self::flag('is_renewed_this_year', 'በዚህ ዓመት ታድሷል'),
                    self::text('address', 'አድራሻ'),
                    self::expert('served_by_expert', 'ያገለገለው ባለሙያ'),
                ],
            ]),
            self::entry('legal-secretaries', 'የህግ ጸሐፊ', '014 ጸሐፊ', LegalSecretaryController::class, LegalSecretary::class, [
                'code' => '014',
                'documents' => true,
                'search' => ['full_name', 'license_number'],
                'columns' => ['full_name', 'license_number', 'licensed_date_ec', 'clients_served_total'],
                'fields' => [
                    self::f('full_name', 'ሙሉ ስም', 'string', true),
                    self::age('age', 'እድሜ'),
                    self::gender('gender', 'ጾታ'),
                    self::f('license_number', 'የፈቃድ ቁጥር', 'string'),
                    self::f('licensed_date_ec', 'ፈቃድ የተሰጠበት ቀን', 'string'),
                    self::count('clients_served_male', 'ወንድ ደንበኞች'),
                    self::count('clients_served_female', 'ሴት ደንበኞች'),
                    self::count('clients_served_total', 'ደንበኞች ድምር'),
                    self::money('service_fee', 'የአገልግሎት ክፍያ'),
                    self::money('license_fee', 'የፈቃድ ክፍያ'),
                    self::flag('is_renewed_this_year', 'በዚህ ዓመት ታድሷል'),
                    self::text('address', 'አድራሻ'),
                    self::expert('served_by_expert', 'ያገለገለው ባለሙያ'),
                ],
            ]),
        ];

        $indexed = [];

        foreach ($registers as $register) {
            $indexed[$register['key']] = $register;
        }

        return $indexed;
    }

    /**
     * @param  class-string  $controller
     * @param  class-string  $model
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private static function entry(string $key, string $title, string $nav, string $controller, string $model, array $extra): array
    {
        return array_merge([
            'key' => $key,
            'uri' => $key,
            'title' => $title,
            'nav' => $nav,
            'controller' => $controller,
            'model' => $model,
            'scoped' => true,
            'review' => false,
            'reviewer' => false,
            'note' => false,
            'publish' => false,
            'project_public' => false,
            'documents' => false,
            'nullable_org' => false,
            'search' => [],
            'columns' => [],
            'fields' => [],
        ], $extra);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private static function f(string $name, string $label, string $type = 'string', bool $required = false, array $extra = []): array
    {
        return array_merge([
            'name' => $name,
            'label' => $label,
            'type' => $type,
            'required' => $required,
        ], $extra);
    }

    /**
     * @return array<string, mixed>
     */
    private static function year(string $name, string $label, bool $required = false): array
    {
        return self::f($name, $label, 'integer', $required, ['min' => 1900, 'max' => 2200]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function age(string $name, string $label): array
    {
        return self::f($name, $label, 'integer', false, ['min' => 0, 'max' => 120]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function count(string $name, string $label): array
    {
        return self::f($name, $label, 'integer', false, ['min' => 0, 'max' => 1000000, 'empty' => 0]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function money(string $name, string $label): array
    {
        return self::f($name, $label, 'decimal', false, ['min' => 0, 'max' => 999999999999.99]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function text(string $name, string $label, bool $required = false): array
    {
        return self::f($name, $label, 'text', $required, ['span' => 'full']);
    }

    /**
     * @return array<string, mixed>
     */
    private static function flag(string $name, string $label): array
    {
        return self::f($name, $label, 'bool', false, ['span' => 'full']);
    }

    /**
     * @return array<string, mixed>
     */
    private static function status(): array
    {
        return self::f('status', 'ሁኔታ', 'enum', true, [
            'enum' => ReviewStatus::class,
            'options' => [ReviewStatus::Draft->value, ReviewStatus::Submitted->value],
            'default' => ReviewStatus::Draft->value,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function gender(string $name, string $label): array
    {
        return self::f($name, $label, 'enum', false, ['enum' => Gender::class]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function unit(bool $required = false): array
    {
        return self::f('work_unit_id', 'የስራ ክፍል', 'work_unit', $required);
    }

    /**
     * @return array<string, mixed>
     */
    private static function expert(string $name, string $label): array
    {
        return self::f($name, $label, 'user', false);
    }

    /**
     * @param  list<array{name: string, label: string, type?: string}>  $columns
     * @return array<string, mixed>
     */
    private static function rows(string $name, string $label, array $columns): array
    {
        return self::f($name, $label, 'rows', false, ['columns' => $columns, 'span' => 'full']);
    }

    /**
     * Name, address, age, and gender for two people, then the register-specific fields.
     *
     * @param  list<array<string, mixed>>  $tail
     * @return list<array<string, mixed>>
     */
    private static function partyPair(string $first, string $firstLabel, string $second, string $secondLabel, array $tail): array
    {
        return array_merge([
            self::f($first.'_name', $firstLabel, 'string', true),
            self::age($first.'_age', 'እድሜ'),
            self::gender($first.'_gender', 'ጾታ'),
            self::text($first.'_address', 'አድራሻ'),
            self::f($second.'_name', $secondLabel, 'string', true),
            self::age($second.'_age', 'እድሜ'),
            self::gender($second.'_gender', 'ጾታ'),
            self::text($second.'_address', 'አድራሻ'),
        ], $tail);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function studyCounts(): array
    {
        $labels = [
            'manuals_prepared' => 'የተዘጋጁ ማኑዋሎች',
            'brochures_prepared' => 'የተዘጋጁ ብሮሸሮች',
            'awareness_platforms_count' => 'የግንዛቤ መድረኮች',
            'reached_kebeles_cities' => 'የተደረሰባቸው ቀበሌዎች/ከተሞች',
            'media_awareness_male' => 'የሚዲያ ግንዛቤ ወንድ',
            'media_awareness_female' => 'የሚዲያ ግንዛቤ ሴት',
            'media_awareness_total' => 'የሚዲያ ግንዛቤ ድምር',
            'reached_schools' => 'የተደረሰባቸው ትምህርት ቤቶች',
            'legal_advice_institutions' => 'የህግ ምክር ተቋማት',
            'legal_advice_individuals' => 'የህግ ምክር ግለሰቦች',
            'legal_advice_total' => 'የህግ ምክር ድምር',
            'total_citizens_male' => 'ጠቅላላ ዜጎች ወንድ',
            'total_citizens_female' => 'ጠቅላላ ዜጎች ሴት',
            'total_citizens_sum' => 'ጠቅላላ ዜጎች ድምር',
            'collected_proclamations' => 'የተሰበሰቡ አዋጆች',
            'collected_regulations' => 'የተሰበሰቡ ደንቦች',
            'collected_directives' => 'የተሰበሰቡ መመሪያዎች',
            'collected_total' => 'የተሰበሰበ ድምር',
            'disseminated_proclamations' => 'የተሰራጩ አዋጆች',
            'disseminated_regulations' => 'የተሰራጩ ደንቦች',
            'disseminated_directives' => 'የተሰራጩ መመሪያዎች',
            'disseminated_total' => 'የተሰራጨ ድምር',
            'drafted_laws_count' => 'የተረቀቁ ህጎች',
            'law_enforcement_study_count' => 'የህግ አስፈጻሚ ጥናት',
            'impact_effective_count' => 'ውጤታማ ተፅዕኖ',
            'impact_ineffective_count' => 'ውጤታማ ያልሆነ ተፅዕኖ',
        ];

        $fields = [];

        foreach ($labels as $name => $label) {
            $fields[] = self::count($name, $label);
        }

        return $fields;
    }
}
