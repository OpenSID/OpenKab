<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Status hasil pemanggilan API Satu Data.
 *
 * Tiga nilai ini adalah pembeda utama antara "data memang kosong" dan
 * "data tidak dapat diperoleh" (FR-005). View WAJIB bercabang berdasarkan
 * nilai ini, bukan berdasarkan apakah array kosong.
 *
 * @method static static Data()
 * @method static static Empty()
 * @method static static Unavailable()
 */
final class DataSpasialStatus extends Enum
{
    /**
     * Respons sukses berisi minimal satu item.
     */
    public const Data = 'data';

    /**
     * Respons sukses tanpa data (FR-015).
     */
    public const Empty = 'empty';

    /**
     * Data tidak dapat diperoleh sementara (FR-014).
     */
    public const Unavailable = 'unavailable';
}
