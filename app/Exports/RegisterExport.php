<?php

namespace App\Exports;

use App\Support\FieldPresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class RegisterExport implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  array<string, mixed>  $definition
     * @param  list<string>  $columns
     */
    public function __construct(
        private array $definition,
        private array $columns,
        private Collection $records,
    ) {}

    public function title(): string
    {
        return mb_substr($this->definition['title'], 0, 31);
    }

    public function headings(): array
    {
        return array_map(
            fn (string $column) => FieldPresenter::heading($this->definition, $column),
            $this->columns,
        );
    }

    public function collection(): Collection
    {
        return $this->records->map(function (Model $record) {
            return array_map(
                fn (string $column) => FieldPresenter::show($record, $column),
                $this->columns,
            );
        });
    }
}
