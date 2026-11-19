<?php

namespace App\Enums;

enum LawCategory: string
{
    use HasValues;

    case Proclamation = 'proclamation';
    case Regulation = 'regulation';
    case Directive = 'directive';
    case Manual = 'manual';
}
