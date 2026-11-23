<?php

namespace App\Services;

use App\Mail\PasswordResetByAdminMail;
use App\Models\User;
use App\Rules\StrongPassword;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Reset password pengguna oleh administrator (research.md R4, R5, R6).
 *
 * Password sementara dibuat acak, disimpan sebagai hash, dan dikirim lewat email.
 * Password plaintext hanya dikembalikan ke pemanggil untuk ditampilkan satu kali.
 */
class PasswordResetByAdminService
{
    private const PANJANG_PASSWORD = 16;

    private const MAKS_PERCOBAAN = 5;

    /**
     * @return array{password: string, email_terkirim: bool}
     */
    public function reset(User $target, User $pelaku): array
    {
        $password = $this->buatPasswordSementara();

        $target->passwordHistoryReason = 'admin_reset';
        $target->password = $password;
        $target->force_password_reset = true;
        $target->save();

        $emailTerkirim = $this->kirimEmail($target, $password);

        activity()
            ->performedOn($target)
            ->causedBy($pelaku)
            ->withProperties([
                'aksi' => 'reset_password',
                'email_terkirim' => $emailTerkirim,
            ])
            ->log('reset_password');

        return [
            'password' => $password,
            'email_terkirim' => $emailTerkirim,
        ];
    }

    private function buatPasswordSementara(): string
    {
        for ($i = 0; $i < self::MAKS_PERCOBAAN; $i++) {
            $kandidat = Str::password(self::PANJANG_PASSWORD);

            if ((new StrongPassword)->passes('password', $kandidat)) {
                return $kandidat;
            }
        }

        throw new RuntimeException('Gagal membuat password sementara yang memenuhi aturan.');
    }

    private function kirimEmail(User $target, string $password): bool
    {
        try {
            Mail::to($target->email)->send(new PasswordResetByAdminMail($target->name, $password));

            return true;
        } catch (\Throwable $e) {
            // Jangan catat alamat email atau password, cukup jenis error untuk operator
            Log::error('Gagal mengirim email reset password oleh administrator', [
                'user_id' => $target->id,
                'exception' => $e::class,
            ]);

            return false;
        }
    }
}
