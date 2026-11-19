<?php

namespace App\Enums;

enum StaffCategory: string
{
    use HasValues;

    case Prosecutor = 'prosecutor';
    case PublicServant = 'public_servant';
}
