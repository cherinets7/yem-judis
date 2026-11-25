<?php

namespace App\Models\Concerns;

use App\Models\RegisterDocument;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasRegisterDocuments
{
    public function documents(): MorphMany
    {
        return $this->morphMany(RegisterDocument::class, 'documentable');
    }
}
