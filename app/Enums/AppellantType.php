<?php

namespace App\Enums;

enum AppellantType: string
{
    use HasValues;

    case Prosecutor = 'prosecutor';
    case Defendant = 'defendant';

    public function label(): string
    {
        return match ($this) {
            self::Prosecutor => 'ዐቃቤ ህግ',
            self::Defendant => 'ተከሳሽ',
        };
    }
}
