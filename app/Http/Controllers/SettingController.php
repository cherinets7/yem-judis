<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(Request $request): View
    {
        return view('settings.edit', [
            'setting' => Setting::current(),
            'canEdit' => Roles::allows($request->user(), 'settings'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'department_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'footer_note' => ['required', 'string', 'max:500'],
            'about_text' => ['nullable', 'string', 'max:4000'],
            'services_text' => ['nullable', 'string', 'max:4000'],
        ]);

        $setting = Setting::current();
        $setting->fill([
            'department_name' => $data['department_name'],
            'address' => $data['address'],
            'phone' => $data['phone'] !== '' ? $data['phone'] : null,
            'email' => $data['email'] !== '' ? $data['email'] : null,
            'footer_note' => $data['footer_note'],
            'about_text' => filled($data['about_text'] ?? null) ? $data['about_text'] : null,
            'services_text' => filled($data['services_text'] ?? null) ? $data['services_text'] : null,
            'updated_by' => $request->user()->id,
        ]);
        $setting->save();

        return redirect()
            ->route('settings.edit')
            ->with('status', 'ቅንብሩ ተቀምጧል።');
    }
}
