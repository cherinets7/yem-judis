<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'required' => ':attribute ያስፈልጋል።',
            'email' => 'ኢሜይል ትክክል አይደለም።',
        ], [
            'email' => 'ኢሜይል',
            'password' => 'የይለፍ ቃል',
        ]);

        $key = Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'በርካታ ሙከራ። እባክዎ ከአንድ ደቂቃ በኋላ ይሞክሩ።',
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => 'ኢሜይል ወይም የይለፍ ቃል ትክክል አይደለም።',
            ]);
        }

        $request->session()->regenerate();

        if (! $request->user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => 'ይህ መለያ ተዘግቷል።',
            ]);
        }

        RateLimiter::clear($key);

        if (TwoFactor::enabled($request->user())) {
            $request->session()->put([
                'login.id' => $request->user()->id,
                'login.remember' => $request->boolean('remember'),
            ]);
            Auth::logout();

            return redirect()->route('two-factor.challenge');
        }

        if (TwoFactor::requiredFor($request->user()) && ! TwoFactor::enabled($request->user())) {
            return redirect()
                ->route('two-factor.edit')
                ->with('status', 'ለእርስዎ ሚና ሁለት ደረጃ ማረጋገጫ ማንቃት ያስፈልጋል።');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
