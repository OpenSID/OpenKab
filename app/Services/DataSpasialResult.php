<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DataSpasialReason;
use App\Enums\DataSpasialStatus;
use InvalidArgumentException;

/**
 * Hasil pemanggilan API Satu Data (FR-005).
 *
 * Value object ini memaksa pemanggil memeriksa `status`, sehingga "data
 * memang kosong" tidak pernah tertukar dengan "data tidak dapat diperoleh"
 * (FR-014 vs FR-015).
 *
 * Immutable: seluruh properti readonly, dibangun lewat named constructor.
 */
final class DataSpasialResult
{
    /**
     * @param  array<int, array<string, mixed>>  $data  Item hasil unwrap dari envelope API
     * @param  array<string, mixed>  $meta  Berisi kunci `pagination` dan `links` bila ada
     */
    private function __construct(
        public readonly DataSpasialStatus $status,
        public readonly array $data,
        public readonly array $meta,
        public readonly ?DataSpasialReason $reason,
        public readonly int $httpStatus,
        public readonly ?string $message,
    ) {
        $this->assertInvariants();
    }

    /**
     * Respons sukses berisi minimal satu item.
     *
     * @param  array<int, array<string, mixed>>  $data
     * @param  array<string, mixed>  $meta
     */
    public static function data(array $data, array $meta = [], ?DataSpasialReason $reason = null, int $httpStatus = 200): self
    {
        return new self(DataSpasialStatus::fromValue(DataSpasialStatus::Data), $data, $meta, $reason, $httpStatus, null);
    }

    /**
     * Respons sukses tanpa data (FR-015).
     *
     * @param  array<string, mixed>  $meta
     */
    public static function empty(array $meta = [], ?DataSpasialReason $reason = null, int $httpStatus = 200): self
    {
        return new self(DataSpasialStatus::fromValue(DataSpasialStatus::Empty), [], $meta, $reason, $httpStatus, null);
    }

    /**
     * Data tidak dapat diperoleh sementara (FR-014).
     *
     * @param  array<string, mixed>  $meta
     */
    public static function unavailable(
        DataSpasialReason $reason,
        int $httpStatus = 0,
        ?string $message = null,
        array $meta = []
    ): self {
        return new self(DataSpasialStatus::fromValue(DataSpasialStatus::Unavailable), [], $meta, $reason, $httpStatus, $message);
    }

    public function isData(): bool
    {
        return $this->status->is(DataSpasialStatus::Data);
    }

    public function isEmpty(): bool
    {
        return $this->status->is(DataSpasialStatus::Empty);
    }

    public function isUnavailable(): bool
    {
        return $this->status->is(DataSpasialStatus::Unavailable);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        return $this->data[0] ?? null;
    }

    public function count(): int
    {
        return count($this->data);
    }

    /**
     * @return array<string, mixed>
     */
    public function pagination(): array
    {
        return $this->meta['pagination'] ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function links(): array
    {
        return $this->meta['links'] ?? [];
    }

    /**
     * Pesan generik untuk pengguna akhir. Tidak pernah menyertakan pesan
     * teknis dari API (FR-014/FR-016).
     */
    public function userMessage(): string
    {
        return DataSpasialReason::userMessage();
    }

    /**
     * Bentuk serializable untuk disimpan pada cache.
     *
     * @return array<string, mixed>
     */
    public function toCachePayload(): array
    {
        return [
            'status' => $this->status->value,
            'data' => $this->data,
            'meta' => $this->meta,
            'http_status' => $this->httpStatus,
        ];
    }

    /**
     * Bangun kembali hasil dari cache. `reason` & `message` sengaja tidak
     * disimpan karena hasil `unavailable` tidak pernah masuk cache.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function fromCachePayload(array $payload): self
    {
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $meta = is_array($payload['meta'] ?? null) ? $payload['meta'] : [];
        $httpStatus = (int) ($payload['http_status'] ?? 200);

        return $data === []
            ? self::empty($meta, null, $httpStatus)
            : self::data($data, $meta, null, $httpStatus);
    }

    /**
     * Konteks teknis untuk log backend. WAJIB tidak dirender ke view.
     *
     * @return array<string, mixed>
     */
    public function logContext(string $endpoint = ''): array
    {
        return array_filter([
            'endpoint' => $endpoint !== '' ? $endpoint : null,
            'status' => $this->status->value,
            'reason' => $this->reason?->value,
            'http_status' => $this->httpStatus,
            'message' => $this->message,
        ], static fn ($value) => $value !== null);
    }

    /**
     * Jaga invariant object agar tidak dapat dibangun salah oleh pemanggil.
     */
    private function assertInvariants(): void
    {
        if ($this->status->is(DataSpasialStatus::Data)) {
            if ($this->data === []) {
                throw new InvalidArgumentException(
                    'DataSpasialResult berstatus data wajib memiliki minimal satu item.'
                );
            }

            if ($this->reason !== null) {
                throw new InvalidArgumentException(
                    'DataSpasialResult berstatus data tidak boleh memiliki reason.'
                );
            }
        }

        if ($this->status->is(DataSpasialStatus::Empty) && $this->reason !== null) {
            throw new InvalidArgumentException(
                'DataSpasialResult berstatus empty tidak boleh memiliki reason.'
            );
        }

        if ($this->status->is(DataSpasialStatus::Unavailable) && $this->reason === null) {
            throw new InvalidArgumentException(
                'DataSpasialResult berstatus unavailable wajib memiliki reason.'
            );
        }
    }
}
