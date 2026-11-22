<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\WorkUnit;
use App\Support\AuditLogger;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkUnitController extends Controller
{
    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $office = $this->office($organization);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('work_units', 'name')->where('organization_id', $office->id)],
        ], [
            'required' => ':attribute ያስፈልጋል።',
            'unique' => 'ይህ የስራ ክፍል ከዚህ በፊት ተመዝግቧል።',
        ], [
            'name' => 'የስራ ክፍል',
        ]);

        $unit = WorkUnit::query()->create([
            'organization_id' => $office->id,
            'name' => trim($data['name']),
            'is_active' => true,
        ]);
        AuditLogger::record($request->user(), $office, 'created', 'የስራ ክፍል ተመዝግቧል፡ '.$unit->name);

        return back()->with('status', 'የስራ ክፍሉ ተመዝግቧል።');
    }

    public function update(Request $request, Organization $organization, WorkUnit $unit): RedirectResponse
    {
        $office = $this->office($organization);
        abort_unless((int) $unit->organization_id === $office->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('work_units', 'name')->where('organization_id', $office->id)->ignore($unit)],
        ], [
            'required' => ':attribute ያስፈልጋል።',
            'unique' => 'ይህ የስራ ክፍል ከዚህ በፊት ተመዝግቧል።',
        ], [
            'name' => 'የስራ ክፍል',
        ]);

        $unit->name = trim($data['name']);
        $unit->save();
        AuditLogger::record($request->user(), $office, 'updated', 'የስራ ክፍል ተዘምኗል፡ '.$unit->name);

        return back()->with('status', 'የስራ ክፍሉ ተዘምኗል።');
    }

    public function deactivate(Request $request, Organization $organization, WorkUnit $unit): RedirectResponse
    {
        $office = $this->office($organization);
        abort_unless((int) $unit->organization_id === $office->id, 404);

        $unit->is_active = ! $unit->is_active;
        $unit->save();
        AuditLogger::record($request->user(), $office, 'updated', $unit->is_active ? 'የስራ ክፍል በርቷል፡ '.$unit->name : 'የስራ ክፍል ጠፍቷል፡ '.$unit->name);

        return back()->with('status', $unit->is_active ? 'የስራ ክፍሉ በርቷል።' : 'የስራ ክፍሉ ጠፍቷል።');
    }

    private function office(Organization $organization): Organization
    {
        abort_unless(in_array($organization->id, OrganizationScope::ids(), true), 404);

        return $organization;
    }
}
