<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = [
        'group',
        'role',
        'allowed',
    ];

    protected $casts = [
        'allowed' => 'boolean',
    ];
}
