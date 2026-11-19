<?php

namespace App\Enums;

enum CommunityPostType: string
{
    use HasValues;

    case News = 'news';
    case Announcement = 'announcement';
    case Feedback = 'feedback';
    case LegalJoke = 'legal_joke';
}
