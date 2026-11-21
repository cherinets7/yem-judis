<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Support\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function edit(): View
    {
        $granted = [];

        foreach (PermissionCatalog::groups() as $group => $definition) {
            $granted[$group] = PermissionCatalog::roles($group);
        }

        return view('permissions.edit', [
            'groups' => PermissionCatalog::groups(),
            'roles' => array_values(array_filter(
                UserRole::cases(),
                fn (UserRole $role): bool => $role !== UserRole::SuperAdmin,
            )),
            'granted' => $granted,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $assignable = PermissionCatalog::assignableRoles();

        $data = $request->validate([
            'granted' => ['nullable', 'array'],
            'granted.*' => ['array'],
            'granted.*.*' => ['string', Rule::in($assignable)],
        ]);

        $granted = $data['granted'] ?? [];

        foreach (array_keys(PermissionCatalog::groups()) as $group) {
            $selected = array_values(array_unique($granted[$group] ?? []));
            PermissionCatalog::sync($group, $selected);
        }

        return redirect()
            ->route('permissions.edit')
            ->with('status', 'ፈቃዶቹ ተቀምጠዋል።');
    }
}
