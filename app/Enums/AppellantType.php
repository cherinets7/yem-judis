<?php

namespace App\Enums;

enum AppellantType: string
{
    use HasValues;

    case Prosecutor = 'prosecutor';
    case Defendant = 'defendant';
}
