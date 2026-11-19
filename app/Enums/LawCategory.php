<?php

namespace App\Enums;

enum LawCategory: string
{
    use HasValues;

    case Proclamation = 'proclamation';
    case Regulation = 'regulation';
    case Directive = 'directive';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Proclamation => 'አዋጅ',
            self::Regulation => 'ደንብ',
            self::Directive => 'መመሪያ',
            self::Manual => 'ማኑዋል',
        };
    }
}
