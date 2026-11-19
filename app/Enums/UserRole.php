<?php

namespace App\Enums;

enum UserRole: string
{
    use HasValues;

    case SuperAdmin = 'super_admin';
    case ZoneHead = 'zone_head';
    case ZonePlanExpert = 'zone_plan_expert';
    case UnitCoordinator = 'unit_coordinator';
    case HrExpert = 'hr_expert';
    case LegalExpert = 'legal_expert';
    case OfficeHead = 'office_head';
    case OfficePlanExpert = 'office_plan_expert';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'ዋና አስተዳዳሪ',
            self::ZoneHead => 'የዞን ኃላፊ',
            self::ZonePlanExpert => 'የዞን ዕቅድ ባለሙያ',
            self::UnitCoordinator => 'የክፍል አስተባባሪ',
            self::HrExpert => 'የሰው ሀብት ባለሙያ',
            self::LegalExpert => 'የህግ ባለሙያ',
            self::OfficeHead => 'የጽህፈት ቤት ኃላፊ',
            self::OfficePlanExpert => 'የጽህፈት ቤት ዕቅድ ባለሙያ',
        };
    }
}
