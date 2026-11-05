<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Penyebab data DataSpasial tidak dapat diperoleh.
 *
 * CATATAN PENTING: enum ini hanya untuk keperluan diagnosa (log backend).
 * `ModuleNotInstalled` dan `ServiceUnavailable` sengaja menghasilkan
 * `DataSpasialStatus::Unavailable` yang sama sehingga UI tidak perlu
 * membedakan keduanya maupun memetakan teks pesan dari API (FR-014).
 *
 * @method static static NotConfigured()
 * @method static static Timeout()
 * @method static static ConnectionFailed()
 * @method static static ServiceUnavailable()
 * @method static static ModuleNotInstalled()
 * @method static static Unauthorized()
 * @method static static SchemaVersionUnsupported()
 * @method static static ClientError()
 */
final class DataSpasialReason extends Enum
{
    /**
     * URL dasar atau token API belum dikonfigurasi (FR-003).
     */
    public const NotConfigured = 'not_configured';

    /**
     * API tidak menjawab dalam batas waktu (FR-004).
     */
    public const Timeout = 'timeout';

    /**
     * Kegagalan koneksi: DNS, TCP, atau TLS.
     */
    public const ConnectionFailed = 'connection_failed';

    /**
     * Status 5xx selain 503.
     */
    public const ServiceUnavailable = 'http_5xx';

    /**
     * Status 503 pada endpoint DataSpasial: modul belum terpasang atau
     * versinya tidak cocok (API-02 / #467).
     */
    public const ModuleNotInstalled = 'module_not_installed';

    /**
     * Status 401 (token ditolak) atau 403 (ability tidak cukup).
     */
    public const Unauthorized = 'unauthorized';

    /**
     * Status 400 dengan error_code SchemaVersionUnsupported.
     */
    public const SchemaVersionUnsupported = 'schema_version_unsupported';

    /**
     * Status 4xx lainnya: 404, 409, 422.
     */
    public const ClientError = 'client_error';

    /**
     * Pesan yang ditampilkan ke pengguna akhir untuk semua penyebab di atas.
     * WAJIB generik — jangan pernah menyertakan detail teknis (FR-014/FR-016).
     */
    public static function userMessage(): string
    {
        return config('dataspasial.unavailable_message')
            ?? 'Data sementara tidak tersedia. Silakan coba lagi nanti.';
    }
}
