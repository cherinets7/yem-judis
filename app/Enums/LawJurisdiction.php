<?php

namespace App\Enums;

enum LawJurisdiction: string
{
    use HasValues;

    case Federal = 'federal';
    case Regional = 'regional';
    case Zonal = 'zonal';

    public function label(): string
    {
        return match ($this) {
            self::Federal => 'ፌዴራል',
            self::Regional => 'ክልል',
            self::Zonal => 'ዞናዊ',
        };
    }
}
