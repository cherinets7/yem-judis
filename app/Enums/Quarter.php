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
}
