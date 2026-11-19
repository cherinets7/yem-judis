<?php

namespace App\Enums;

enum PlanDocumentType: string
{
    use HasValues;

    case AnnualPlan = 'annual_plan';
    case AnnualReport = 'annual_report';

    public function label(): string
    {
        return match ($this) {
            self::AnnualPlan => 'ዓመታዊ እቅድ',
            self::AnnualReport => 'ዓመታዊ ሪፖርት',
        };
    }
}
