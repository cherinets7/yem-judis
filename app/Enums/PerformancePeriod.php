<?php

namespace App\Enums;

enum PerformancePeriod: string
{
    use HasValues;

    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Semiannual = 'semiannual';

    public function label(): string
    {
        return match ($this) {
            self::Weekly => 'ሳምንታዊ',
            self::Monthly => 'ወራዊ',
            self::Quarterly => 'የሩብ ዓመት',
            self::Semiannual => 'የስድስት ወር',
        };
    }

    public function numberLabel(): string
    {
        return match ($this) {
            self::Weekly => 'ሳምንት',
            self::Monthly => 'ወር',
            self::Quarterly => 'ሩብ',
            self::Semiannual => 'ክፍል',
        };
    }

    public function max(): int
    {
        return match ($this) {
            self::Weekly => 53,
            self::Monthly => 12,
            self::Quarterly => 4,
            self::Semiannual => 2,
        };
    }
}
