<?php

namespace App\Enums;

enum ReviewStatus: string
{
    use HasValues;

    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'ረቂቅ',
            self::Submitted => 'ቀርቧል',
            self::Approved => 'ጸድቋል',
            self::Rejected => 'ተቀባይነት አላገኘም',
        };
    }
}
