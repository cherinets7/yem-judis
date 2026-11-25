<?php

namespace App\Support;

use App\Enums\CourtLevel;
use App\Enums\FinalOutcome;
use App\Enums\PublicCaseType;
use App\Models\CivilAppeal;
use App\Models\CivilCase;
use App\Models\CivilExecution;
use App\Models\CriminalAppeal;
use App\Models\CriminalCase;
use App\Models\PublicCaseTracking;
use Illuminate\Database\Eloquent\Model;

class PublicCaseProjector
{
    public static function sync(Model $record): ?PublicCaseTracking
    {
        $attributes = self::attributes($record);

        if ($attributes === null || ! method_exists($record, 'publicTracking')) {
            return null;
        }

        $tracking = $record->publicTracking()->first();

        if ($tracking === null) {
            return $record->publicTracking()->create($attributes);
        }

        $tracking->fill($attributes);
        $tracking->save();

        return $tracking;
    }

    public static function forget(Model $record): void
    {
        if (! method_exists($record, 'publicTracking')) {
            return;
        }

        $record->publicTracking()->delete();
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function attributes(Model $record): ?array
    {
        $mapped = match ($record::class) {
            CriminalCase::class => self::criminal($record),
            CriminalAppeal::class => self::criminalAppeal($record),
            CivilCase::class => self::civil($record),
            CivilAppeal::class => self::civilAppeal($record),
            CivilExecution::class => self::execution($record),
            default => null,
        };

        if ($mapped === null) {
            return null;
        }

        return [
            'organization_id' => $record->organization_id,
            'expert_id' => $record->expert_id,
            'prosecutor_file_number' => self::clip($mapped['prosecutor_file_number'], 'ያልተሞላ'),
            'court_file_number' => self::clip($mapped['court_file_number']),
            'plaintiff_name' => self::clip($mapped['plaintiff_name']),
            'defendant_name' => self::clip($mapped['defendant_name']),
            'case_type' => $mapped['case_type'],
            'court_level' => $mapped['court_level'],
            'final_outcome' => $mapped['final_outcome'],
            'appointment_date_ec' => self::clip($record->getAttribute('appointment_date_ec')),
            'appointment_reason' => self::clip($record->getAttribute('appointment_reason')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function criminal(CriminalCase $record): array
    {
        return [
            'prosecutor_file_number' => $record->prosecutor_file_number,
            'court_file_number' => $record->court_file_number,
            'plaintiff_name' => $record->complainant_and_police_date,
            'defendant_name' => $record->defendant_names,
            'case_type' => PublicCaseType::CriminalRegular->value,
            'court_level' => $record->court_level?->value ?? CourtLevel::WoredaCourt->value,
            'final_outcome' => self::outcome(
                filled($record->court_decision_type),
                (bool) $record->closed_indefinitely,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function criminalAppeal(CriminalAppeal $record): array
    {
        return [
            'prosecutor_file_number' => $record->prosecutor_appeal_number,
            'court_file_number' => $record->appellate_court_and_file_no ?: $record->lower_court_file_number,
            'plaintiff_name' => $record->appellant_name,
            'defendant_name' => $record->respondent_name,
            'case_type' => PublicCaseType::CriminalAppeal->value,
            'court_level' => CourtLevel::HighCourt->value,
            'final_outcome' => self::outcome(filled($record->decision_date_and_outcome)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function civil(CivilCase $record): array
    {
        return [
            'prosecutor_file_number' => $record->prosecutor_case_number ?: $record->court_file_number,
            'court_file_number' => $record->court_file_number,
            'plaintiff_name' => $record->plaintiff_name,
            'defendant_name' => $record->defendant_name,
            'case_type' => PublicCaseType::CivilRegular->value,
            'court_level' => $record->court_level?->value ?? CourtLevel::WoredaCourt->value,
            'final_outcome' => self::outcome(filled($record->decision_date_and_type)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function civilAppeal(CivilAppeal $record): array
    {
        return [
            'prosecutor_file_number' => $record->prosecutor_appeal_number,
            'court_file_number' => $record->appellate_court_file_number ?: $record->lower_court_file_number,
            'plaintiff_name' => $record->appellant_name ?: $record->appellant_type?->label(),
            'defendant_name' => $record->respondent_name,
            'case_type' => PublicCaseType::CivilAppeal->value,
            'court_level' => CourtLevel::HighCourt->value,
            'final_outcome' => self::outcome(filled($record->appellate_decision_and_date)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function execution(CivilExecution $record): array
    {
        return [
            'prosecutor_file_number' => $record->prosecutor_file_number,
            'court_file_number' => $record->court_file_number,
            'plaintiff_name' => $record->execution_plaintiff,
            'defendant_name' => $record->execution_defendant,
            'case_type' => PublicCaseType::Execution->value,
            'court_level' => CourtLevel::WoredaCourt->value,
            'final_outcome' => self::outcome(filled($record->court_decision_date_and_summary)),
        ];
    }

    private static function outcome(bool $decided, bool $closed = false): string
    {
        if ($closed) {
            return FinalOutcome::ClosedIndefinitely->value;
        }

        return $decided ? FinalOutcome::Decided->value : FinalOutcome::Pending->value;
    }

    private static function clip(mixed $value, ?string $fallback = null): ?string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return $fallback;
        }

        return mb_substr($text, 0, 255);
    }
}
