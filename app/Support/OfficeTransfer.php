<?php

namespace App\Support;

use App\Models\AnnualPlan;
use App\Models\Attorney;
use App\Models\AuditLog;
use App\Models\CivicAssociation;
use App\Models\CivilAppeal;
use App\Models\CivilCase;
use App\Models\CivilExecution;
use App\Models\CommunityPost;
use App\Models\Contract;
use App\Models\CriminalAppeal;
use App\Models\CriminalCase;
use App\Models\LegalSecretary;
use App\Models\LegalStudiesDissemination;
use App\Models\Organization;
use App\Models\PerformanceReport;
use App\Models\PowerOfAttorneyGrant;
use App\Models\PowerOfAttorneyRevocation;
use App\Models\ProsecutorWeeklyReport;
use App\Models\PublicCaseTracking;
use App\Models\StaffProfile;
use App\Models\StrategicFocusData;
use App\Models\User;
use App\Models\WeeklyReport;
use App\Models\WorkUnit;

class OfficeTransfer
{
    public static function count(Organization $office): int
    {
        $total = User::query()->where('organization_id', $office->id)->count();

        foreach (self::models() as $model) {
            $total += $model::query()->where('organization_id', $office->id)->count();
        }

        return $total;
    }

    public static function move(Organization $from, Organization $to): void
    {
        foreach (WorkUnit::query()->where('organization_id', $from->id)->get() as $unit) {
            $match = WorkUnit::query()
                ->where('organization_id', $to->id)
                ->where('name', $unit->name)
                ->first();

            if ($match === null) {
                $unit->organization_id = $to->id;
                $unit->save();

                continue;
            }

            User::query()->where('work_unit_id', $unit->id)->update(['work_unit_id' => $match->id]);
            WeeklyReport::query()->where('work_unit_id', $unit->id)->update(['work_unit_id' => $match->id]);
            StaffProfile::query()->where('work_unit_id', $unit->id)->update(['work_unit_id' => $match->id]);
            StrategicFocusData::query()->where('work_unit_id', $unit->id)->update(['work_unit_id' => $match->id]);
            $unit->is_active = false;
            $unit->save();
        }

        User::query()->where('organization_id', $from->id)->update(['organization_id' => $to->id]);

        foreach (self::models() as $model) {
            $model::query()->where('organization_id', $from->id)->update(['organization_id' => $to->id]);
        }
    }

    /**
     * @return list<class-string>
     */
    private static function models(): array
    {
        return [
            AnnualPlan::class,
            WeeklyReport::class,
            StrategicFocusData::class,
            StaffProfile::class,
            CommunityPost::class,
            CriminalCase::class,
            CriminalAppeal::class,
            CivilCase::class,
            CivilAppeal::class,
            CivilExecution::class,
            PowerOfAttorneyGrant::class,
            PowerOfAttorneyRevocation::class,
            Contract::class,
            LegalStudiesDissemination::class,
            CivicAssociation::class,
            Attorney::class,
            LegalSecretary::class,
            ProsecutorWeeklyReport::class,
            PublicCaseTracking::class,
            PerformanceReport::class,
            AuditLog::class,
        ];
    }
}
