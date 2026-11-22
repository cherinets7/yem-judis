<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\AuditLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('auth.password');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', PasswordRule::min(8)],
        ], [
            'required' => ':attribute ያስፈልጋል።',
            'confirmed' => 'የይለፍ ቃሉ አልተመሳሰለም።',
            'min' => ':attribute ቢያንስ :min ፊደል ይሁን።',
        ], [
            'current_password' => 'የአሁኑ የይለፍ ቃል',
            'password' => 'አዲስ የይለፍ ቃል',
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'የአሁኑ የይለፍ ቃል ትክክል አይደለም።',
            ]);
        }

        $user->password = $data['password'];
        $user->save();
        AuditLogger::record($user, $user, 'password', 'የይለፍ ቃል ተቀይሯል።');

        return back()->with('status', 'የይለፍ ቃሉ ተቀይሯል።');
    }

    public function requestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'required' => ':attribute ያስፈልጋል።',
            'email' => 'ኢሜይል ትክክል አይደለም።',
        ], [
            'email' => 'ኢሜይል',
        ]);

        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'መለያው ካለ የመቀየሪያ አገናኝ ወደ ኢሜይሉ ይላካል።');
    }

    public function resetForm(string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request('email'),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'confirmed', PasswordRule::min(8)],
        ], [
            'required' => ':attribute ያስፈልጋል።',
            'email' => 'ኢሜይል ትክክል አይደለም።',
            'confirmed' => 'የይለፍ ቃሉ አልተመሳሰለም።',
            'min' => ':attribute ቢያንስ :min ፊደል ይሁን።',
        ], [
            'email' => 'ኢሜይል',
            'password' => 'አዲስ የይለፍ ቃል',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
                AuditLogger::record($user, $user, 'password', 'የረሳው የይለፍ ቃል ተቀይሯል።');
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => 'የመቀየሪያው አገናኝ አልሰራም። እንደገና ይጠይቁ።',
            ]);
        }

        return redirect()->route('login')->with('status', 'የይለፍ ቃሉ ተቀይሯል። ይግቡ።');
    }
}
