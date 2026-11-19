<?php

namespace App\Enums;

enum PublicCaseType: string
{
    use HasValues;

    case CriminalRegular = 'criminal_regular';
    case CriminalAppeal = 'criminal_appeal';
    case CivilRegular = 'civil_regular';
    case CivilAppeal = 'civil_appeal';
    case Execution = 'execution';

    public function label(): string
    {
        return match ($this) {
            self::CriminalRegular => 'መደበኛ ወንጀል',
            self::CriminalAppeal => 'የወንጀል ይግባኝ',
            self::CivilRegular => 'መደበኛ ፍትሐ ብሔር',
            self::CivilAppeal => 'የፍትሐ ብሔር ይግባኝ',
            self::Execution => 'ፍርድ ትግበራ',
        };
    }
}
