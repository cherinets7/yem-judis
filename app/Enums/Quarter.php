<?php

namespace App\Enums;

enum Quarter: string
{
    use HasValues;

    case Q1 = 'Q1';
    case Q2 = 'Q2';
    case Q3 = 'Q3';
    case Q4 = 'Q4';
    case Annual = 'Annual';

    public function label(): string
    {
        return match ($this) {
            self::Q1 => 'የመጀመሪያ ሩብ',
            self::Q2 => 'ሁለተኛ ሩብ',
            self::Q3 => 'ሦስተኛ ሩብ',
            self::Q4 => 'አራተኛ ሩብ',
            self::Annual => 'ዓመታዊ',
        };
    }
}
