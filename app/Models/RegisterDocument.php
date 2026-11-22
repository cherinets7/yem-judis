<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RegisterDocument extends Model
{
    protected $fillable = [
        'title',
        'file_path',
        'uploaded_by',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function downloadName(): string
    {
        $extension = pathinfo($this->file_path, PATHINFO_EXTENSION);
        $stem = trim(str_replace(['/', '\\'], '-', $this->title));

        return $extension !== '' ? $stem.'.'.$extension : $stem;
    }
}
