<?php

namespace Tests\Feature;

use App\Enums\OrganizationType;
use App\Enums\ReviewStatus;
use App\Exports\RegisterExport;
use App\Models\Organization;
use App\Models\ProsecutorWeeklyReport;
use App\Models\User;
use App\Support\ExportRecords;
use App\Support\RegisterCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ProsecutorWeeklyExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_civil_matrix_is_stored_and_reviewed_inside_scope(): void
    {
        $planner = User::query()->where('email', 'office.plan@yemjustice.test')->firstOrFail();
        $zonePlan = User::query()->where('email', 'zone.plan@yemjustice.test')->firstOrFail();
        $zoneHead = User::query()->where('email', 'zone.head@yemjustice.test')->firstOrFail();
        $officeHead = User::query()->where('email', 'office.head@yemjustice.test')->firstOrFail();
        $hr = User::query()->where('email', 'hr.expert@yemjustice.test')->firstOrFail();
        $legal = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();

        $this->actingAs($planner)->get(route('admin.prosecutor-weekly.create', ['type' => 'civil']))
            ->assertOk()
            ->assertSee('መንግስት ከሳሽ')
            ->assertSee('በፍ/ቤት ያሉ')
            ->assertSee('ይግባኝ')
            ->assertSee('አፈፃፀም')
            ->assertSee('ውል ምዝገባ')
            ->assertSee('የህግ ምክር')
            ->assertSee('አቤቱታ')
            ->assertSee('የማጥራት ምጣኔ')
            ->assertSee('የመርታት ምጣኔ')
            ->assertSee('የማስቀጣት ምጣኔ');

        $this->actingAs($planner)->get(route('admin.prosecutor-weekly.create', ['type' => 'criminal']))
            ->assertOk()
            ->assertSee('ም/መዝገብ ውሳኔ')
            ->assertSee('ማረፊያ ቤት (3 ጊዜ በሳምንት)')
            ->assertSee('ማረሚያ ቤት (1 ጊዜ በወር)')
            ->assertSee('አቤቱታና ህግ ምክር');

        $this->actingAs($planner)->get(route('admin.prosecutor-weekly.create', ['type' => 'document_registration']))
            ->assertOk()
            ->assertSee('የሰነዶች ህጋዊነት')
            ->assertSee('ሲቪክ ማህበራት')
            ->assertSee('ጠበቆችና ህግ ጉዳይ ፀሐፊዎች');

        $this->actingAs($hr)->get(route('admin.prosecutor-weekly.index'))->assertForbidden();

        $payload = $this->payload($planner, 12);
        $this->actingAs($planner)->post(route('admin.prosecutor-weekly.store'), $payload)
            ->assertRedirect(route('admin.prosecutor-weekly.index', ['type' => 'civil']));

        $report = ProsecutorWeeklyReport::query()->firstOrFail();
        $this->assertSame(21, $report->items()->count());
        $row = $report->items->first(fn ($item) => $item->section_code === 'civil_plaintiff' && ($item->metrics['row'] ?? null) === 'woreda');
        $this->assertSame(20, $row->metrics['total']);
        $this->assertEquals(50, $row->metrics['clearance_rate']);
        $this->assertEquals(75, $row->metrics['win_rate']);
        $this->assertEquals(50, $row->metrics['conviction_rate']);

        $this->actingAs($planner)->post(route('admin.prosecutor-weekly.store'), $payload)
            ->assertSessionHasErrors('week_number');

        $this->actingAs($zonePlan)->post(route('admin.prosecutor-weekly.approve', $report))->assertForbidden();
        $this->actingAs($zoneHead)->post(route('admin.prosecutor-weekly.approve', $report))->assertRedirect();
        $report->refresh();
        $this->assertSame(ReviewStatus::Approved, $report->status);
        $this->assertSame($zoneHead->id, $report->reviewed_by);

        $fofa = Organization::query()->where('type', OrganizationType::Woreda)->where('name', 'like', '%ፎፋ%')->firstOrFail();
        $this->actingAs($zonePlan)->post(route('admin.prosecutor-weekly.store'), $this->payload($zonePlan, 3, [
            'organization_id' => $fofa->id,
            'report_type' => 'criminal',
            'rows' => [
                'criminal_investigation' => [
                    'person' => ['brought_forward' => 4, 'new_intake' => 6, 'charged' => 3, 'closed' => 2, 'returned' => 1],
                ],
            ],
        ]))->assertRedirect();

        $other = ProsecutorWeeklyReport::query()->where('organization_id', $fofa->id)->firstOrFail();
        $this->actingAs($officeHead)->get(route('admin.prosecutor-weekly.edit', $other))->assertNotFound();
        $this->actingAs($officeHead)->get(route('admin.prosecutor-weekly.pdf', $other))->assertNotFound();

        $pdf = $this->actingAs($planner)->get(route('admin.prosecutor-weekly.pdf', $report));
        $pdf->assertOk();
        $pdf->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $this->assertStringContainsString('NotoSansEthiopic', $pdf->getContent());

        $excel = $this->actingAs($planner)->get(route('admin.prosecutor-weekly.excel', $report));
        $excel->assertOk();
        $excel->assertDownload('prosecutor-weekly-'.$report->id.'.xlsx');

        $this->actingAs($legal)->get(route('admin.exports.pdf', 'laws'))->assertNotFound();
        $this->actingAs($legal)->get(route('admin.exports.pdf', 'staff-profiles'))->assertForbidden();
        $this->actingAs($hr)->get(route('admin.staff-profiles.index'))->assertSee('PDF ወርድ')->assertSee('ኤክሴል ወርድ');
        $this->actingAs($legal)->get(route('admin.criminal-cases.index'))->assertSee('PDF ወርድ');
        $this->actingAs($planner)->get(route('admin.annual-plans.index'))->assertDontSee('PDF ወርድ');

        $staffPdf = $this->actingAs($hr)->get(route('admin.exports.pdf', 'staff-profiles'));
        $staffPdf->assertOk();
        $this->assertStringStartsWith('%PDF', $staffPdf->getContent());
        $this->assertStringContainsString('NotoSansEthiopic', $staffPdf->getContent());

        $definition = RegisterCatalog::get('criminal-cases');
        $bytes = Excel::raw(
            new RegisterExport($definition, ExportRecords::columns($definition), collect()),
            ExcelFormat::XLSX,
        );
        $this->assertStringStartsWith('PK', $bytes);
        $path = tempnam(sys_get_temp_dir(), 'register').'.xlsx';
        file_put_contents($path, $bytes);
        $sheet = IOFactory::load($path)->getActiveSheet();
        $this->assertSame('ተቋም', $sheet->getCell('A1')->getValue());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(User $user, int $week, array $overrides = []): array
    {
        return array_replace_recursive([
            'organization_id' => $user->organization_id,
            'report_type' => 'civil',
            'budget_year_ec' => 2019,
            'week_number' => $week,
            'period_covered_ec' => 'ኅዳር 4–10',
            'status' => ReviewStatus::Submitted->value,
            'rows' => [
                'civil_plaintiff' => [
                    'woreda' => [
                        'brought_forward' => 10,
                        'new_intake' => 10,
                        'won' => 6,
                        'lost' => 2,
                        'closed' => 2,
                        'punished' => 3,
                    ],
                ],
            ],
        ], $overrides);
    }
}
