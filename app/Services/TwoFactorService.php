<?php

namespace App\Services;

use App\Models\User;

class TwoFactorService
{
    /**
     * Mendapatkan status 2FA user
     */
    public function getUserTwoFactorStatus(User $user)
    {
        return [
            'enabled' => $user->{'2fa_enabled'},
            'channel' => $user->{'2fa_enabled'} ? json_decode($user->{'2fa_channel'}, true) : null,
            'identifier' => $user->{'2fa_enabled'} ? $user->{'2fa_identifier'} : null,
        ];
    }

    /**
     * Aktifkan 2FA untuk user
     */
    public function enableTwoFactor(User $user, string $channel, string $identifier)
    {
        $user->update([
            '2fa_enabled' => true,
            '2fa_channel' => json_encode([$channel]),
            '2fa_identifier' => $identifier,
        ]);

        return true;
    }

    /**
     * Nonaktifkan 2FA untuk user
     */
    public function disableTwoFactor(User $user)
    {
        $user->update([
            '2fa_enabled' => false,
            '2fa_channel' => null,
            '2fa_identifier' => null,
        ]);

        // Hapus semua token OTP yang ada
        $user->otpTokens()->delete();

        return true;
    }

    /**
     * Periksa apakah user memiliki 2FA aktif
     */
    public function hasTwoFactorEnabled(User $user)
    {
        return $user->{'2fa_enabled'};
    }

    /**
     * Dapatkan channel 2FA yang aktif
     */
    public function getTwoFactorChannels(User $user)
    {
        return $user->{'2fa_enabled'} ? json_decode($user->{'2fa_channel'}, true) : [];
    }

    /**
     * Dapatkan identifier 2FA
     */
    public function getTwoFactorIdentifier(User $user)
    {
        return $user->{'2fa_enabled'} ? $user->{'2fa_identifier'} : null;
    }
}
