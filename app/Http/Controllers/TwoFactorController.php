<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AuditLogger;
use App\Support\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function challenge(): View
    {
        abort_unless(session()->has('login.id'), 403);

        return view('auth.two-factor-challenge');
    }

    public function confirmChallenge(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('login.id');
        abort_unless($userId, 403);

        $data = $request->validate([
            'code' => ['required', 'string'],
        ], [
            'required' => ':attribute ያስፈልጋል።',
        ], [
            'code' => 'ኮድ',
        ]);

        $user = User::query()->whereKey($userId)->firstOrFail();

        if (! TwoFactor::verify($user, $data['code'])) {
            throw ValidationException::withMessages([
                'code' => 'ኮዱ ትክክል አይደለም።',
            ]);
        }

        Auth::login($user, (bool) $request->session()->pull('login.remember'));
        $request->session()->forget('login.id');
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function edit(Request $request): View
    {
        $user = $request->user();
        abort_unless(TwoFactor::requiredFor($user), 403);

        $secret = $request->session()->get('two_factor_setup_secret');

        if (! TwoFactor::enabled($user) && ! is_string($secret)) {
            $secret = TwoFactor::makeSecret();
            $request->session()->put('two_factor_setup_secret', $secret);
        }

        return view('auth.two-factor', [
            'enabled' => TwoFactor::enabled($user),
            'secret' => is_string($secret) ? $secret : null,
            'qr' => is_string($secret) ? TwoFactor::qrSvg($user, $secret) : null,
            'recovery' => $request->session()->get('two_factor_recovery_codes', []),
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless(TwoFactor::requiredFor($user), 403);

        $secret = $request->session()->get('two_factor_setup_secret');
        abort_unless(is_string($secret) && $secret !== '', 404);

        $data = $request->validate([
            'code' => ['required', 'string'],
        ], [
            'required' => ':attribute ያስፈልጋል።',
        ], [
            'code' => 'ኮድ',
        ]);

        if (! TwoFactor::codeMatches($secret, $data['code'])) {
            throw ValidationException::withMessages([
                'code' => 'ኮዱ ትክክል አይደለም።',
            ]);
        }

        $codes = TwoFactor::recoveryCodes();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $codes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget('two_factor_setup_secret');
        $request->session()->flash('two_factor_recovery_codes', $codes);
        AuditLogger::record($user, $user, 'security', 'ሁለት ደረጃ ማረጋገጥ በርቷል።');

        return redirect()->route('two-factor.edit')->with('status', 'ሁለት ደረጃ ማረጋገጥ በርቷል። የመልሶ ማግኛ ኮዶችን ያስቀምጡ።');
    }

    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless(TwoFactor::requiredFor($user), 403);

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
        $request->session()->forget(['two_factor_setup_secret', 'two_factor_recovery_codes']);
        AuditLogger::record($user, $user, 'security', 'ሁለት ደረጃ ማረጋገጥ ጠፍቷል።');

        return back()->with('status', 'ሁለት ደረጃ ማረጋገጥ ጠፍቷል።');
    }
}
