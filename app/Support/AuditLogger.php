<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * @param  array<string, mixed>  $changes
     */
    public static function record(?User $user, ?Model $subject, string $action, string $summary, array $changes = []): AuditLog
    {
        return AuditLog::query()->create([
            'user_id' => $user?->id,
            'organization_id' => $subject?->getAttribute('organization_id')
                ?? ($subject instanceof Organization ? $subject->id : null),
            'auditable_type' => $subject ? $subject::class : null,
            'auditable_id' => $subject?->getKey(),
            'action' => $action,
            'summary' => mb_substr($summary, 0, 255),
            'changes' => $changes === [] ? null : self::plain($changes),
        ]);
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, mixed>
     */
    public static function plain(array $changes): array
    {
        $plain = [];

        foreach ($changes as $key => $value) {
            if (in_array($key, ['updated_at', 'password'], true)) {
                continue;
            }

            $plain[$key] = self::value($value);
        }

        return $plain;
    }

    private static function value(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof Model) {
            return $value->getKey();
        }

        if (is_array($value)) {
            return self::plain($value);
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return $value;
    }
}
