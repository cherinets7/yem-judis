<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RegisterSearch
{
    /**
     * @param  list<int>  $organizationIds
     * @return list<array{definition: array<string, mixed>, records: Collection<int, Model>}>
     */
    public static function lookup(string $query, array $organizationIds): array
    {
        $term = trim($query);

        if (mb_strlen($term) < 2) {
            return [];
        }

        $like = '%'.addcslashes($term, '%_\\').'%';
        $groups = [];

        foreach (RegisterCatalog::all() as $definition) {
            $columns = $definition['search'] ?? [];

            if ($columns === []) {
                continue;
            }

            $records = collect();

            if (method_exists($definition['model'], 'search')) {
                try {
                    $records = $definition['model']::search($term)
                        ->take(40)
                        ->get()
                        ->filter(function ($record) use ($definition, $organizationIds) {
                            if (! $definition['scoped']) {
                                return true;
                            }

                            $organizationId = $record->getAttribute('organization_id');

                            if ($organizationId === null) {
                                return (bool) ($definition['nullable_org'] ?? false);
                            }

                            return in_array((int) $organizationId, $organizationIds, true);
                        })
                        ->take(8)
                        ->values();
                } catch (\Throwable) {
                    $records = collect();
                }
            }

            if ($records->isEmpty()) {
                $builder = $definition['model']::query();

                if ($definition['scoped']) {
                    $builder->with('organization');
                    $builder->where(function ($inner) use ($definition, $organizationIds): void {
                        $inner->whereIn('organization_id', $organizationIds);

                        if ($definition['nullable_org']) {
                            $inner->orWhereNull('organization_id');
                        }
                    });
                }

                $builder->where(function ($inner) use ($columns, $like): void {
                    foreach ($columns as $column) {
                        $inner->orWhere($column, 'like', $like);
                    }
                });

                $records = $builder->latest()->limit(8)->get();
            }

            if ($records->isEmpty()) {
                continue;
            }

            $groups[] = [
                'definition' => $definition,
                'records' => $records,
            ];
        }

        return $groups;
    }

    public static function title(Model $record, array $definition): string
    {
        foreach ($definition['search'] as $column) {
            $value = trim((string) $record->getAttribute($column));

            if ($value !== '') {
                return mb_substr($value, 0, 120);
            }
        }

        return $definition['title'];
    }
}
