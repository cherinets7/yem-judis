<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactor
{
    public static function requiredFor(?User $user): bool
    {
        $role = Roles::of($user);

        return in_array($role, [UserRole::SuperAdmin, UserRole::ZoneHead, UserRole::OfficeHead], true);
    }

    public static function enabled(User $user): bool
    {
        return filled($user->two_factor_secret) && $user->two_factor_confirmed_at !== null;
    }

    public static function makeSecret(): string
    {
        return (new Google2FA)->generateSecretKey();
    }

    /**
     * @return list<string>
     */
    public static function recoveryCodes(): array
    {
        return Collection::times(8, fn () => Str::lower(Str::random(4).'-'.Str::random(4)))->all();
    }

    public static function qrSvg(User $user, string $secret): string
    {
        $otpauth = (new Google2FA)->getQRCodeUrl(
            (string) config('app.name'),
            $user->email,
            $secret,
        );

        $writer = new Writer(new ImageRenderer(
            new RendererStyle(192),
            new SvgImageBackEnd,
        ));

        return $writer->writeString($otpauth);
    }

    public static function codeMatches(string $secret, string $code): bool
    {
        $code = str_replace(' ', '', $code);

        return (new Google2FA)->verifyKey($secret, $code);
    }

    public static function verify(User $user, string $code): bool
    {
        $secret = $user->two_factor_secret;

        if (! is_string($secret) || $secret === '') {
            return false;
        }

        $code = str_replace(' ', '', $code);

        if (self::codeMatches($secret, $code)) {
            return true;
        }

        $codes = $user->two_factor_recovery_codes ?? [];

        if (! is_array($codes)) {
            return false;
        }

        foreach ($codes as $index => $recovery) {
            if (! hash_equals((string) $recovery, $code)) {
                continue;
            }

            unset($codes[$index]);
            $user->forceFill([
                'two_factor_recovery_codes' => array_values($codes),
            ])->save();

            return true;
        }

        return false;
    }
}
