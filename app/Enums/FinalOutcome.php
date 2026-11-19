<?php

namespace App\Enums;

enum FinalOutcome: string
{
    use HasValues;

    case Pending = 'pending';
    case Decided = 'decided';
    case ClosedIndefinitely = 'closed_indefinitely';
}
