<?php

namespace App\Models\Concerns;

use App\Support\RegisterCatalog;
use Laravel\Scout\Searchable;

trait SearchableRegister
{
    use Searchable;

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $payload = ['id' => $this->getKey()];

        foreach ($this->searchableColumns() as $column) {
            $payload[$column] = (string) ($this->getAttribute($column) ?? '');
        }

        if ($this->getAttribute('organization_id') !== null) {
            $payload['organization_id'] = (int) $this->organization_id;
        }

        return $payload;
    }

    /**
     * @return list<string>
     */
    private function searchableColumns(): array
    {
        foreach (RegisterCatalog::all() as $definition) {
            if ($definition['model'] !== static::class) {
                continue;
            }

            return $definition['search'] ?? [];
        }

        return [];
    }
}
