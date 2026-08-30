<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static SUCCESS()
 * @method static static FAILED()
 */
final class SsoStatusEnum extends Enum
{
    public const SUCCESS = 'success';

    public const FAILED = 'failed';

    /**
     * Kode alasan kegagalan untuk kolom reason_if_failed.
     */
    public const REASON_BUKAN_ADMIN = 'bukan_admin';

    public const REASON_2FA_BELUM_VERIFIKASI = '2fa_belum_verifikasi';

    public const REASON_2FA_NONAKTIF = '2fa_nonaktif';

    public const REASON_AKUN_NONAKTIF = 'akun_nonaktif';

    public const REASON_AKUN_TERKUNCI = 'akun_terkunci';

    public const REASON_DESA_INVALID = 'desa_invalid';

    public const REASON_RATE_LIMITED = 'rate_limited';

    public const REASON_TOKEN_INVALID = 'token_invalid';

    public const REASON_TOKEN_EXPIRED = 'token_expired';

    public const REASON_TOKEN_REPLAY = 'token_replay';

    public const REASON_SIGNATURE_INVALID = 'signature_invalid';

    public const REASON_CALLBACK_UNAUTHORIZED = 'callback_unauthorized';

    public const REASON_OPEN_SID_UNREACHABLE = 'opensid_unreachable';

    public const REASON_ORIGIN_INVALID = 'origin_invalid';

    public const REASON_UNKNOWN = 'unknown';

    /**
     * Override method all().
     */
    public static function all(): array
    {
        return [
            self::SUCCESS => 'Berhasil',
            self::FAILED => 'Gagal',
        ];
    }
}
