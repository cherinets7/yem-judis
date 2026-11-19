<?php

namespace App\Enums;

enum LawJurisdiction: string
{
    use HasValues;

    case Federal = 'federal';
    case Regional = 'regional';
    case Zonal = 'zonal';
}
