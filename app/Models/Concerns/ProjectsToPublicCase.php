<?php

namespace App\Models\Concerns;

use App\Models\PublicCaseTracking;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait ProjectsToPublicCase
{
    public function publicTracking(): MorphOne
    {
        return $this->morphOne(PublicCaseTracking::class, 'source');
    }

    public function getIsPublishedAttribute(): bool
    {
        return (bool) $this->publicTracking?->is_published;
    }
}
