<?php

namespace App\Enums;

enum ProsecutorReportType: string
{
    use HasValues;

    case Civil = 'civil';
    case Criminal = 'criminal';
    case DocumentRegistration = 'document_registration';

    public function label(): string
    {
        return match ($this) {
            self::Civil => 'ፍትሐ ብሔር',
            self::Criminal => 'ወንጀል',
            self::DocumentRegistration => 'ሰነድ ምዝገባ',
        };
    }
}
