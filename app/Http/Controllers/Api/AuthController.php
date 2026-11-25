<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'code' => ['nullable', 'string'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if ($user === null || ! Hash::check($data['password'], $user->password) || ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['ኢሜይል ወይም የይለፍ ቃል ትክክል አይደለም።'],
            ]);
        }

        if (TwoFactor::enabled($user)) {
            if (! isset($data['code']) || ! TwoFactor::verify($user, $data['code'])) {
                throw ValidationException::withMessages([
                    'code' => ['የሁለት ደረጃ ኮድ ያስፈልጋል።'],
                ]);
            }
        }

        $token = $user->createToken($data['device_name'] ?? 'mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->payload($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->payload($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['status' => 'ok']);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->displayName(),
            'email' => $user->email,
            'role' => $user->role?->value,
            'organization_id' => $user->organization_id,
            'two_factor' => TwoFactor::enabled($user),
        ];
    }
}
