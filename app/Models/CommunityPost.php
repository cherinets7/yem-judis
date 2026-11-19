<?php

namespace App\Models;

use App\Enums\CommunityPostType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityPost extends Model
{
    protected $fillable = [
        'type',
        'title',
        'content',
        'organization_id',
        'user_id',
        'guest_name',
        'is_published',
    ];

    protected $casts = [
        'type' => CommunityPostType::class,
        'is_published' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
