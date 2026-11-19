<?php

namespace App\Enums;

enum OrganizationType: string
{
    use HasValues;

    case Zone = 'zone';
    case Woreda = 'woreda';
    case City = 'city';

    public function label(): string
    {
        return match ($this) {
            self::Zone => 'ዞን',
            self::Woreda => 'ወረዳ',
            self::City => 'ከተማ',
        };
    }
}
