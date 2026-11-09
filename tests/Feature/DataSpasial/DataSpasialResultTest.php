<?php

declare(strict_types=1);

use App\Enums\DataSpasialReason;
use App\Enums\DataSpasialStatus;
use App\Services\DataSpasialResult;

it('menandai hasil berisi data dan menyimpan item serta meta', function () {
    $items = [
        ['id' => '1', 'type' => 'ds_sektor', 'attributes' => ['kode' => 'pendidikan']],
        ['id' => '2', 'type' => 'ds_sektor', 'attributes' => ['kode' => 'kesehatan']],
    ];
    $meta = ['pagination' => ['total' => 2, 'count' => 2, 'per_page' => 10, 'current_page' => 1, 'total_pages' => 1]];

    $result = DataSpasialResult::data($items, $meta);

    expect($result->status)->toBeInstanceOf(DataSpasialStatus::class)
        ->and($result->status->value)->toBe('data')
        ->and($result->isData())->toBeTrue()
        ->and($result->isEmpty())->toBeFalse()
        ->and($result->isUnavailable())->toBeFalse()
        ->and($result->reason)->toBeNull()
        ->and($result->httpStatus)->toBe(200)
        ->and($result->data)->toBe($items)
        ->and($result->count())->toBe(2)
        ->and($result->first())->toBe($items[0])
        ->and($result->pagination())->toBe($meta['pagination']);
});

it('menandai hasil kosong tanpa dianggap gagal', function () {
    $meta = ['pagination' => ['total' => 0, 'count' => 0]];

    $result = DataSpasialResult::empty($meta);

    expect($result->status)->toBeInstanceOf(DataSpasialStatus::class)
        ->and($result->status->value)->toBe('empty')
        ->and($result->isEmpty())->toBeTrue()
        ->and($result->isData())->toBeFalse()
        ->and($result->isUnavailable())->toBeFalse()
        ->and($result->reason)->toBeNull()
        ->and($result->data)->toBe([])
        ->and($result->count())->toBe(0)
        ->and($result->first())->toBeNull()
        ->and($result->pagination())->toBe($meta['pagination']);
});

it('menandai hasil tidak tersedia dengan penyebab', function () {
    $result = DataSpasialResult::unavailable(
        DataSpasialReason::fromValue(DataSpasialReason::ModuleNotInstalled),
        503,
        'Modul DataSpasial belum terpasang atau versinya tidak cocok'
    );

    expect($result->status)->toBeInstanceOf(DataSpasialStatus::class)
        ->and($result->status->value)->toBe('unavailable')
        ->and($result->isUnavailable())->toBeTrue()
        ->and($result->isData())->toBeFalse()
        ->and($result->isEmpty())->toBeFalse()
        ->and($result->reason)->toBeInstanceOf(DataSpasialReason::class)
        ->and($result->reason->value)->toBe('module_not_installed')
        ->and($result->httpStatus)->toBe(503)
        ->and($result->message)->toBe('Modul DataSpasial belum terpasang atau versinya tidak cocok')
        ->and($result->data)->toBe([])
        ->and($result->count())->toBe(0)
        ->and($result->first())->toBeNull()
        ->and($result->pagination())->toBe([]);
});

it('menyediakan pesan generik untuk pengguna dan tidak membocorkan pesan teknis', function () {
    $result = DataSpasialResult::unavailable(
        DataSpasialReason::fromValue(DataSpasialReason::Timeout),
        0,
        'cURL error 28: Operation timed out after 10001 milliseconds'
    );

    expect($result->userMessage())->toBe('Data sementara tidak tersedia. Silakan coba lagi nanti.')
        ->and($result->userMessage())->not->toContain('cURL error');
});

it('memberikan pesan generik yang sama untuk semua penyebab', function () {
    $messages = [];

    foreach (DataSpasialReason::getValues() as $reason) {
        $messages[] = DataSpasialResult::unavailable(DataSpasialReason::fromValue($reason))->userMessage();
    }

    expect($messages)->toHaveCount(count(DataSpasialReason::getValues()))
        ->and(array_unique($messages))->toHaveCount(1);
});

it('mengembalikan pagination kosong bila meta tidak tersedia', function () {
    expect(DataSpasialResult::data([['id' => '1']])->pagination())->toBe([])
        ->and(DataSpasialResult::empty()->pagination())->toBe([]);
});

it('menolak data kosong pada status data', function () {
    DataSpasialResult::data([]);
})->throws(InvalidArgumentException::class);

it('menolak alasan pada status data dan kosong', function () {
    expect(fn () => DataSpasialResult::data([['id' => '1']], [], DataSpasialReason::fromValue(DataSpasialReason::Timeout)))
        ->toThrow(InvalidArgumentException::class)
        ->and(fn () => DataSpasialResult::empty([], DataSpasialReason::fromValue(DataSpasialReason::Timeout)))
        ->toThrow(InvalidArgumentException::class);
});

it('menolak hasil tidak tersedia tanpa alasan', function () {
    $constructor = (new ReflectionClass(DataSpasialResult::class))->getConstructor();
    $constructor->setAccessible(true);

    expect(fn () => $constructor->invokeArgs(
        (new ReflectionClass(DataSpasialResult::class))->newInstanceWithoutConstructor(),
        [
            DataSpasialStatus::fromValue(DataSpasialStatus::Unavailable),
            [],
            [],
            null,
            0,
            null,
        ]
    ))->toThrow(InvalidArgumentException::class);
});

it('menyembunyikan constructor agar hanya dibangun lewat named constructor', function () {
    expect((new ReflectionClass(DataSpasialResult::class))->getConstructor()->isPrivate())->toBeTrue();
});
