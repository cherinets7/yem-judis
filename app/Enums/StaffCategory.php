<?php

namespace App\Enums;

enum StaffCategory: string
{
    use HasValues;

    case Prosecutor = 'prosecutor';
    case PublicServant = 'public_servant';

    public function label(): string
    {
        return match ($this) {
            self::Prosecutor => 'ዐቃቤ ህግ',
            self::PublicServant => 'ፐብሊክ ሰርቫንት',
        };
    }
}
