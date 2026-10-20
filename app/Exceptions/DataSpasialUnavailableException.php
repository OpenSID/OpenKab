<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\DataSpasialReason;
use Exception;
use Throwable;

/**
 * Penanda internal bahwa data DataSpasial tidak dapat diperoleh sementara.
 *
 * Exception ini dipakai agar method internal di dalam service dapat keluar
 * lebih awal tanpa menduplikasi penanganan kegagalan. Exception ini TIDAK
 * PERNAH boleh lepas ke controller — pemanggil publik selalu memperoleh
 * `App\Services\DataSpasialResult` dengan status `unavailable`.
 */
class DataSpasialUnavailableException extends Exception
{
    public function __construct(
        public readonly DataSpasialReason $reason,
        public readonly string $endpoint = '',
        public readonly int $httpStatus = 0,
        ?string $message = null,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            $message ?? $reason->value,
            0,
            $previous
        );
    }

    /**
     * Pesan yang aman ditampilkan ke pengguna akhir (FR-014).
     */
    public function userMessage(): string
    {
        return DataSpasialReason::userMessage();
    }
}
