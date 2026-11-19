<?php

namespace App\Support;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FieldPresenter
{
    public static function show(Model $record, string $column): string
    {
        if ($column === 'organization_id') {
            $organization = $record->organization;

            return $organization?->amharicName() ?? '—';
        }

        if ($column === 'work_unit_id' && method_exists($record, 'workUnit')) {
            $name = $record->workUnit?->name;

            return is_string($name) && $name !== '' ? self::plain($name) : '—';
        }

        $value = $record->getAttribute($column);

        if ($value instanceof BackedEnum && method_exists($value, 'label')) {
            return $value->label();
        }

        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        if (is_bool($value)) {
            return $value ? 'አዎ' : 'አይ';
        }

        if (is_array($value)) {
            return count($value).' ረድፎች';
        }

        if ($value === null || $value === '') {
            return '—';
        }

        return Str::limit(trim((string) $value), 80);
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    public static function heading(array $definition, string $column): string
    {
        if ($column === 'organization_id') {
            return 'ተቋም';
        }

        foreach ($definition['fields'] as $field) {
            if ($field['name'] === $column) {
                return $field['label'];
            }
        }

        return $column;
    }

    private static function plain(string $name): string
    {
        $plain = preg_replace('/\s*\([^)]*\)\s*$/u', '', $name);

        return trim(is_string($plain) ? $plain : $name);
    }
}
