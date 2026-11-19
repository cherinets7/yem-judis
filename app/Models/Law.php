<?php

namespace App\Models;

use App\Enums\LawCategory;
use App\Enums\LawJurisdiction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Law extends Model
{
    protected $fillable = [
        'jurisdiction',
        'category',
        'title',
        'law_number',
        'issued_year_ec',
        'description',
        'file_path',
        'uploaded_by',
    ];

    protected $casts = [
        'jurisdiction' => LawJurisdiction::class,
        'category' => LawCategory::class,
        'issued_year_ec' => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
