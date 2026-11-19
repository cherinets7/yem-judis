<?php

namespace App\Exports;

use App\Support\FieldPresenter;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class StaffProfileExport implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(private Collection $profiles) {}

    public function title(): string
    {
        return 'የሰው ሀብት';
    }

    public function headings(): array
    {
        return ['ተቋም', 'መደብ', 'ሙሉ ስም', 'የሥራ መደብ', 'የአሁን ደመወዝ', 'የደመወዝ ታሪክ', 'የአፈጻጸም ግምገማ', 'ዕድገት'];
    }

    public function collection(): Collection
    {
        return $this->profiles->map(function ($profile) {
            return [
                FieldPresenter::show($profile, 'organization_id'),
                FieldPresenter::show($profile, 'staff_category'),
                $profile->full_name,
                $profile->job_title ?: '—',
                $profile->current_salary ?: '—',
                $this->history($profile->salary_history, fn (array $row) => trim(($row['year_ec'] ?? '').' '.($row['amount'] ?? '').' '.($row['note'] ?? ''))),
                $this->history($profile->performance_evaluations, fn (array $row) => trim(($row['year_ec'] ?? '').' '.($row['result'] ?? '').' '.($row['note'] ?? ''))),
                $this->history($profile->promotions, fn (array $row) => trim(($row['year_ec'] ?? '').' '.($row['from_title'] ?? '').' → '.($row['to_title'] ?? ''))),
            ];
        });
    }

    /**
     * @param  callable(array<string, mixed>): string  $format
     */
    private function history(mixed $rows, callable $format): string
    {
        if (! is_array($rows) || $rows === []) {
            return '—';
        }

        return collect($rows)
            ->map(fn ($row) => is_array($row) ? $format($row) : '')
            ->filter()
            ->implode('፤ ');
    }
}
