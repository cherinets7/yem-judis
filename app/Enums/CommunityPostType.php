<?php

namespace App\Enums;

enum CommunityPostType: string
{
    use HasValues;

    case News = 'news';
    case Announcement = 'announcement';
    case Feedback = 'feedback';
    case LegalJoke = 'legal_joke';

    public function label(): string
    {
        return match ($this) {
            self::News => 'ዜና',
            self::Announcement => 'ማስታወቂያ',
            self::Feedback => 'አስተያየት',
            self::LegalJoke => 'የህግ ቀልድ',
        };
    }
}
