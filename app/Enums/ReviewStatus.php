<?php

namespace App\Enums;

enum ReviewStatus: string
{
    use HasValues;

    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
