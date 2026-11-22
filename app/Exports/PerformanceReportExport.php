<?php

namespace App\Exports;

use App\Models\PerformanceReport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PerformanceReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  Collection<int, PerformanceReport>  $reports
     */
    public function __construct(private Collection $reports) {}

    public function title(): string
    {
        return 'አፈጻጸም';
    }

    public function headings(): array
    {
        return ['ባለሙያ', 'ተቋም', 'ወቅት', 'ዓመት', 'ተራ', 'የታቀደ', 'የተከናወነ', 'ፈተና', 'ሁኔታ'];
    }

    public function collection(): Collection
    {
        return $this->reports->map(fn (PerformanceReport $report): array => [
            $report->user?->displayName(),
            $report->organization?->amharicName(),
            $report->period->label(),
            $report->budget_year_ec,
            $report->period_number,
            $report->planned,
            $report->accomplished,
            $report->challenges,
            $report->status->label(),
        ]);
    }
}
