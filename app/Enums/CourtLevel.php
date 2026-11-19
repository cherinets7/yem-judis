<?php

namespace App\Enums;

enum CourtLevel: string
{
    use HasValues;

    /** Register code 001. */
    case WoredaCourt = 'woreda_court';

    /** Register code 002 (criminal) and 004 (civil). */
    case HighCourt = 'high_court';
}
