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
}
