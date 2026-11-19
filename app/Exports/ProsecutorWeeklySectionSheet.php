<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProsecutorWeeklySectionSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  array<string, mixed>  $section
     */
    public function __construct(
        private array $section,
        private string $sheetTitle,
    ) {}

    public function title(): string
    {
        return mb_substr($this->sheetTitle, 0, 31);
    }

    public function headings(): array
    {
        return array_merge(
            ['ረድፍ'],
            array_map(fn (array $column) => $column['label'], $this->section['columns']),
        );
    }

    public function array(): array
    {
        return array_map(function (array $row) {
            $line = [$row['label']];

            foreach ($this->section['columns'] as $column) {
                $value = $row['metrics'][$column['key']] ?? null;
                $line[] = self::display($column['key'], $value);
            }

            return $line;
        }, $this->section['rows']);
    }

    public static function display(string $key, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (str_ends_with($key, '_rate')) {
            return $value.'%';
        }

        return (string) $value;
    }
}
