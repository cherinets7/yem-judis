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
}
