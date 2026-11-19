<?php

namespace App\Enums;

enum OrganizationType: string
{
    use HasValues;

    case Zone = 'zone';
    case Woreda = 'woreda';
    case City = 'city';
}
