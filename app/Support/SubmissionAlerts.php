<?php

namespace App\Support;

use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\ReportSubmitted;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SubmissionAlerts
{
    public static function notify(Model $record, string $title, string $body, string $url): void
    {
        $status = $record->getAttribute('status');

        if ($status !== ReviewStatus::Submitted && $status !== ReviewStatus::Submitted->value) {
            return;
        }

        foreach (self::reviewers($record) as $reviewer) {
            $reviewer->notify(new ReportSubmitted($title, $body, $url));
        }
    }

    /**
     * @return Collection<int, User>
     */
    public static function reviewers(Model $record): Collection
    {
        $record->loadMissing('organization');
        $organization = $record->getAttribute('organization');

        if ($organization === null) {
            return new Collection;
        }

        $ids = array_values(array_unique(array_filter([
            (int) $organization->id,
            $organization->parent_id ? (int) $organization->parent_id : null,
        ])));

        $authorId = (int) $record->getAttribute('user_id');

        return User::query()
            ->whereIn('organization_id', $ids)
            ->whereIn('role', [UserRole::ZoneHead->value, UserRole::OfficeHead->value])
            ->where('is_active', true)
            ->when($authorId > 0, fn ($query) => $query->where('id', '!=', $authorId))
            ->get();
    }
}
