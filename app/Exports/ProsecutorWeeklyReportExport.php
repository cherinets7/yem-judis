<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProsecutorWeeklyReportExport implements WithMultipleSheets
{
    /**
     * @param  list<array<string, mixed>>  $reports
     */
    public function __construct(private array $reports) {}

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->reports as $payload) {
            $report = $payload['report'];
            $prefix = $report->budget_year_ec.'-'.$report->week_number;

            foreach ($payload['matrix']['sections'] as $index => $section) {
                $sheets[] = new ProsecutorWeeklySectionSheet(
                    $section,
                    $prefix.'-'.($index + 1),
                );
            }
        }

        if ($sheets === []) {
            $sheets[] = new ProsecutorWeeklySectionSheet([
                'columns' => [],
                'rows' => [],
            ], 'ባዶ');
        }

        return $sheets;
    }
}
