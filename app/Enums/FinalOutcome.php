<?php

namespace App\Enums;

enum FinalOutcome: string
{
    use HasValues;

    case Pending = 'pending';
    case Decided = 'decided';
    case ClosedIndefinitely = 'closed_indefinitely';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'በመጠባበቅ ላይ',
            self::Decided => 'ተወስኗል',
            self::ClosedIndefinitely => 'ላልተወሰነ ጊዜ ተዘግቷል',
        };
    }
}
