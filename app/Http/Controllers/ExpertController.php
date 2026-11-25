<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\CivilAppeal;
use App\Models\CivilCase;
use App\Models\CivilExecution;
use App\Models\CriminalAppeal;
use App\Models\CriminalCase;
use App\Models\Organization;
use App\Models\PublicCaseTracking;
use App\Models\User;
use App\Models\WorkUnit;
use App\Support\AuditLogger;
use App\Support\ExpertProfile;
use App\Support\OrganizationScope;
use App\Support\PermissionCatalog;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExpertController extends Controller
{
    public function index(Request $request): View
    {
        $term = trim($request->string('q')->toString());
        $experts = User::query()
            ->with(['organization', 'workUnit'])
            ->whereIn('organization_id', OrganizationScope::ids())
            ->where('role', '!=', UserRole::SuperAdmin)
            ->when($term !== '', function ($query) use ($term): void {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $query->where(function ($inner) use ($like): void {
                    $inner->where('name', 'like', $like)->orWhere('email', 'like', $like);
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('experts.index', [
            'experts' => $experts,
            'q' => $term,
        ]);
    }

    public function create(): View
    {
        return view('experts.form', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $expert = User::query()->create($this->validated($request) + [
            'email_verified_at' => now(),
        ]);
        ExpertProfile::sync($expert, $request->user());
        AuditLogger::record($request->user(), $expert, 'created', 'ባለሙያ ተመዝግቧል፡ '.$expert->displayName());

        return redirect()
            ->route('admin.experts.show', $expert)
            ->with('status', 'ባለሙያው ተመዝግቧል።');
    }

    public function show(User $user): View
    {
        $expert = $this->find($user);
        $expert->load(['organization', 'workUnit', 'performanceReports', 'staffProfile']);

        return view('experts.show', [
            'expert' => $expert,
            'links' => $this->links($expert),
            'successors' => User::query()
                ->whereIn('organization_id', OrganizationScope::ids())
                ->where('role', '!=', UserRole::SuperAdmin)
                ->where('is_active', true)
                ->whereKeyNot($expert->id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function edit(User $user): View
    {
        return view('experts.form', $this->formData($this->find($user)));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $expert = $this->find($user);
        $expert->update($this->validated($request, $expert));
        ExpertProfile::sync($expert, $request->user());
        AuditLogger::record($request->user(), $expert, 'updated', 'ባለሙያ ተዘምኗል፡ '.$expert->displayName());

        return redirect()
            ->route('admin.experts.show', $expert)
            ->with('status', 'ተዘምኗል።');
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        $expert = $this->find($user);
        abort_if($expert->is(auth()->user()), 403, 'የራስዎን መለያ ማጥፋት አይቻልም።');

        if ($expert->is_active && $this->assignedCount($expert) > 0) {
            $successor = $this->successor($request, $expert);
            $this->transfer($expert, $successor);
            AuditLogger::record($request->user(), $expert, 'updated', 'ጉዳዮች ወደ '.$successor->displayName().' ተዘዋውረዋል።');
        }

        $expert->is_active = ! $expert->is_active;
        $expert->save();
        ExpertProfile::sync($expert, $request->user());
        AuditLogger::record($request->user(), $expert, 'updated', $expert->is_active ? 'ባለሙያ በርቷል።' : 'ባለሙያ ጠፍቷል።');

        return back()->with('status', $expert->is_active ? 'በድጋሚ በርቷል።' : 'ጠፍቷል። ጉዳዮቹ ከነበሩ ተዘዋውረዋል።');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $expert = $this->find($user);
        abort_if($expert->is(auth()->user()), 403, 'የራስዎን የይለፍ ቃል ከዚህ አይቀይሩ።');

        $data = $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ], [
            'required' => ':attribute ያስፈልጋል።',
            'confirmed' => 'የይለፍ ቃሉ አልተመሳሰለም።',
            'min' => ':attribute ቢያንስ :min ፊደል ይሁን።',
        ], [
            'password' => 'አዲስ የይለፍ ቃል',
        ]);

        $expert->password = $data['password'];
        $expert->save();
        AuditLogger::record($request->user(), $expert, 'password', 'የይለፍ ቃል ለባለሙያ ተቀይሯል።');

        return back()->with('status', 'የይለፍ ቃሉ ተቀይሯል።');
    }

    private function find(User $user): User
    {
        abort_unless(in_array((int) $user->organization_id, OrganizationScope::ids(), true), 404);
        abort_if(Roles::of($user) === UserRole::SuperAdmin, 404);

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $expert = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($expert)],
            'password' => [$expert ? 'nullable' : 'required', 'string', Password::min(8)],
            'role' => ['required', Rule::in(PermissionCatalog::assignableRoles())],
            'organization_id' => ['required', 'integer', Rule::in(OrganizationScope::ids())],
            'work_unit_id' => ['required', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'required' => ':attribute ያስፈልጋል።',
            'email' => ':attribute ትክክል አይደለም።',
            'unique' => 'ይህ ኢሜይል ከዚህ በፊት ተመዝግቧል።',
            'in' => ':attribute ትክክል አይደለም።',
            'min' => ':attribute ቢያንስ :min ፊደል ይሁን።',
        ], [
            'name' => 'ስም',
            'email' => 'ኢሜይል',
            'password' => 'የይለፍ ቃል',
            'role' => 'ሚና',
            'organization_id' => 'ተቋም',
            'work_unit_id' => 'የስራ ክፍል',
        ]);

        $unit = WorkUnit::query()
            ->whereKey($data['work_unit_id'])
            ->where('organization_id', $data['organization_id'])
            ->where(function ($query) use ($expert): void {
                $query->where('is_active', true);

                if ($expert?->work_unit_id) {
                    $query->orWhere('id', $expert->work_unit_id);
                }
            })
            ->first();

        if ($unit === null) {
            throw ValidationException::withMessages([
                'work_unit_id' => 'የስራ ክፍሉ ከተቋሙ ጋር አይገናኝም።',
            ]);
        }

        $organization = Organization::query()->whereKey($data['organization_id'])->first();

        if ($organization !== null && ! $organization->is_active && (int) $expert?->organization_id !== (int) $organization->id) {
            throw ValidationException::withMessages([
                'organization_id' => 'የጠፋ ተቋም ላይ ባለሙያ መመዝገብ አይቻልም።',
            ]);
        }

        $payload = [
            'name' => trim($data['name']),
            'email' => $data['email'],
            'role' => $data['role'],
            'organization_id' => (int) $data['organization_id'],
            'work_unit_id' => $unit->id,
            'is_active' => $request->boolean('is_active', $expert?->is_active ?? true),
        ];

        if ($expert === null || ($data['password'] ?? '') !== '') {
            $payload['password'] = $data['password'];
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?User $expert = null): array
    {
        $ids = OrganizationScope::ids();

        return [
            'expert' => $expert,
            'organizations' => Organization::query()
                ->whereIn('id', $ids)
                ->where(function ($query) use ($expert): void {
                    $query->where('is_active', true);

                    if ($expert?->organization_id) {
                        $query->orWhere('id', $expert->organization_id);
                    }
                })
                ->orderBy('name')
                ->get(),
            'workUnits' => WorkUnit::query()
                ->whereIn('organization_id', $ids)
                ->where(function ($query) use ($expert): void {
                    $query->where('is_active', true);

                    if ($expert?->work_unit_id) {
                        $query->orWhere('id', $expert->work_unit_id);
                    }
                })
                ->orderBy('name')
                ->get(),
            'roles' => array_map(
                fn (string $role) => UserRole::from($role),
                PermissionCatalog::assignableRoles(),
            ),
        ];
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function links(User $expert): array
    {
        $counts = [
            'የወንጀል ጉዳይ' => CriminalCase::query()->where('expert_id', $expert->id)->count(),
            'የወንጀል ይግባኝ' => CriminalAppeal::query()->where('expert_id', $expert->id)->count(),
            'የፍትሐ ብሔር ጉዳይ' => CivilCase::query()->where('expert_id', $expert->id)->count(),
            'የፍትሐ ብሔር ይግባኝ' => CivilAppeal::query()->where('expert_id', $expert->id)->count(),
            'ፍርድ ትግበራ' => CivilExecution::query()->where('expert_id', $expert->id)->count(),
            'የፐብሊክ ጉዳይ' => PublicCaseTracking::query()->where('expert_id', $expert->id)->count(),
        ];

        return collect($counts)
            ->map(fn (int $count, string $label): array => ['label' => $label, 'count' => $count])
            ->values()
            ->all();
    }

    private function assignedCount(User $expert): int
    {
        return array_sum(array_map(
            fn (string $model): int => $model::query()->where('expert_id', $expert->id)->count(),
            $this->caseModels(),
        ));
    }

    private function successor(Request $request, User $expert): User
    {
        $data = $request->validate([
            'transfer_to' => ['required', 'integer'],
        ], [
            'required' => 'ጉዳዩን የሚረከብ ባለሙያ ይምረጡ።',
        ], [
            'transfer_to' => 'ተረካቢ',
        ]);

        $successor = User::query()
            ->whereKey($data['transfer_to'])
            ->whereIn('organization_id', OrganizationScope::ids())
            ->where('role', '!=', UserRole::SuperAdmin)
            ->where('is_active', true)
            ->whereKeyNot($expert->id)
            ->first();

        if ($successor === null) {
            throw ValidationException::withMessages([
                'transfer_to' => 'ተረካቢው ባለሙያ አልተገኘም።',
            ]);
        }

        return $successor;
    }

    private function transfer(User $from, User $to): void
    {
        foreach ($this->caseModels() as $model) {
            $model::query()->where('expert_id', $from->id)->update(['expert_id' => $to->id]);
        }
    }

    /**
     * @return list<class-string>
     */
    private function caseModels(): array
    {
        return [
            CriminalCase::class,
            CriminalAppeal::class,
            CivilCase::class,
            CivilAppeal::class,
            CivilExecution::class,
            PublicCaseTracking::class,
        ];
    }
}
