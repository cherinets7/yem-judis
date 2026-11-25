<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationType;
use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\WorkUnit;
use App\Support\AuditLogger;
use App\Support\OfficeTransfer;
use App\Support\OrganizationScope;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OfficeController extends Controller
{
    public function index(): View
    {
        $offices = Organization::query()
            ->with(['parent', 'workUnits'])
            ->withCount(['users', 'children'])
            ->whereIn('id', OrganizationScope::ids())
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('offices.index', [
            'offices' => $offices,
        ]);
    }

    public function create(): View
    {
        return view('offices.form', [
            'office' => null,
            'zones' => $this->zones(),
            'canCreateZone' => $this->canCreateZone(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $office = Organization::query()->create($data);
        $this->seedUnits($office);

        return redirect()
            ->route('admin.offices.show', $office)
            ->with('status', 'ተመዝግቧል።');
    }

    public function show(Organization $organization): View
    {
        $office = $this->find($organization);
        $office->load(['parent', 'children', 'workUnits', 'users']);

        return view('offices.show', [
            'office' => $office,
            'recordCount' => OfficeTransfer::count($office),
            'destinations' => Organization::query()
                ->whereIn('id', OrganizationScope::ids())
                ->where('is_active', true)
                ->whereKeyNot($office->id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function edit(Organization $organization): View
    {
        $office = $this->find($organization);

        return view('offices.form', [
            'office' => $office,
            'zones' => $this->zones(),
            'canCreateZone' => $this->canCreateZone() || $office->type === OrganizationType::Zone,
        ]);
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $office = $this->find($organization);
        $office->update($this->validated($request, $office));

        return redirect()
            ->route('admin.offices.show', $office)
            ->with('status', 'ተዘምኗል።');
    }

    public function deactivate(Request $request, Organization $organization): RedirectResponse
    {
        $office = $this->find($organization);

        if ($office->is_active && OfficeTransfer::count($office) > 0) {
            $destination = $this->destination($request, $office);
            OfficeTransfer::move($office, $destination);
            AuditLogger::record($request->user(), $office, 'updated', 'መዝገቦች ወደ '.$destination->amharicName().' ተዘዋውረዋል።');
        }

        $office->is_active = ! $office->is_active;
        $office->save();
        AuditLogger::record($request->user(), $office, 'updated', $office->is_active ? 'ጽህፈት ቤት በርቷል።' : 'ጽህፈት ቤት ጠፍቷል።');

        return back()->with('status', $office->is_active ? 'በድጋሚ በርቷል።' : 'ጠፍቷል። መዝገቦቹ ከነበሩ ተዘዋውረዋል።');
    }

    private function destination(Request $request, Organization $office): Organization
    {
        $data = $request->validate([
            'transfer_to' => ['required', 'integer'],
        ], [
            'required' => 'መዝገቡን የሚረከብ ጽህፈት ቤት ይምረጡ።',
        ], [
            'transfer_to' => 'ተረካቢ',
        ]);

        $destination = Organization::query()
            ->whereKey($data['transfer_to'])
            ->whereIn('id', OrganizationScope::ids())
            ->where('is_active', true)
            ->whereKeyNot($office->id)
            ->first();

        if ($destination === null) {
            throw ValidationException::withMessages([
                'transfer_to' => 'ተረካቢው ጽህፈት ቤት አልተገኘም።',
            ]);
        }

        return $destination;
    }

    private function find(Organization $organization): Organization
    {
        abort_unless(in_array($organization->id, OrganizationScope::ids(), true), 404);

        return $organization;
    }

    /**
     * @return array{name: string, type: string, parent_id: int|null, is_active: bool}
     */
    private function validated(Request $request, ?Organization $office = null): array
    {
        $types = $this->canCreateZone() || $office?->type === OrganizationType::Zone
            ? OrganizationType::values()
            : [OrganizationType::Woreda->value, OrganizationType::City->value];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in($types)],
            'parent_id' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'required' => ':attribute ያስፈልጋል።',
            'in' => ':attribute ትክክል አይደለም።',
        ], [
            'name' => 'ስም',
            'type' => 'አይነት',
            'parent_id' => 'ዞን',
        ]);

        $type = OrganizationType::from($data['type']);
        $parentId = null;

        if ($type !== OrganizationType::Zone) {
            $parent = Organization::query()
                ->whereKey($request->input('parent_id'))
                ->where('type', OrganizationType::Zone)
                ->whereIn('id', OrganizationScope::ids())
                ->first();

            if ($parent === null) {
                throw ValidationException::withMessages([
                    'parent_id' => 'ወረዳው የሚገባበት ዞን ይምረጡ።',
                ]);
            }

            $parentId = $parent->id;
        }

        return [
            'name' => trim($data['name']),
            'type' => $type,
            'parent_id' => $parentId,
            'is_active' => $request->boolean('is_active', $office?->is_active ?? true),
        ];
    }

    /**
     * @return Collection<int, Organization>
     */
    private function zones()
    {
        return Organization::query()
            ->whereIn('id', OrganizationScope::ids())
            ->where('type', OrganizationType::Zone)
            ->orderBy('name')
            ->get();
    }

    private function canCreateZone(): bool
    {
        return Roles::of(auth()->user()) === UserRole::SuperAdmin;
    }

    private function seedUnits(Organization $office): void
    {
        $names = $office->type === OrganizationType::Zone
            ? [
                'የመምሪያ ኃላፊ ጽህፈት ቤት',
                'ዕቅድ፣ ክትትልና ግምገማ ሥራ ክፍል',
                'የሰው ኃይል አስተዳደር ሥራ ክፍል',
                'የዐቃቤ ህግ ማስተባበሪያ ሥራ ክፍል',
            ]
            : [
                'የጽህፈት ቤት ኃላፊ',
                'ዕቅድና ሪፖርት',
                'ክርክር ክትትል',
                'የሰነድ ምዝገባ',
            ];

        foreach ($names as $name) {
            WorkUnit::query()->create([
                'organization_id' => $office->id,
                'name' => $name,
            ]);
        }
    }
}
