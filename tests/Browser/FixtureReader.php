<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\Enums\StatistikPendudukEnum;

final class FixtureReader
{
    private const FIXTURES_DIR = __DIR__ . '/fixtures';

    private static array $cache = [];

    public static function read(string $filename): ?array
    {
        if (isset(self::$cache[$filename])) {
            return self::$cache[$filename];
        }

        $path = self::FIXTURES_DIR . '/' . $filename;
        if (!file_exists($path)) {
            return null;
        }

        self::$cache[$filename] = json_decode(file_get_contents($path), true);

        return self::$cache[$filename];
    }

    public static function kabupatenNames(): array
    {
        $data = self::read('kabupaten.json');

        return array_map(fn (array $item) => $item['nama_kabupaten'], $data ?? []);
    }

    public static function firstKabupatenKode(): ?string
    {
        $data = self::read('kabupaten.json');

        return $data ? $data[0]['kode_kabupaten'] : null;
    }

    public static function categoriesValues(): array
    {
        $data = self::read('data-website.json');
        $items = $data['data']['categoriesItems'] ?? [];

        return array_map(fn (array $item) => (string) $item['value'], $items);
    }

    public static function demografiChartKeys(): array
    {
        $files = glob(self::FIXTURES_DIR . '/statistik-penduduk-*.json');
        $keys = [];
        foreach ($files as $file) {
            $basename = basename($file, '.json');
            $key = substr($basename, strlen('statistik-penduduk-'));
            $keys[] = $key;
        }

        return $keys;
    }

    public static function demografiChartLabels(): array
    {
        $kategori = StatistikPendudukEnum::KATEGORI_STATISTIK;
        $keys = self::demografiChartKeys();
        $labels = [];
        foreach ($keys as $key) {
            if (isset($kategori[$key])) {
                $labels[$key] = $kategori[$key];
            }
        }

        return $labels;
    }

    public static function demografiChartData(string $key): ?array
    {
        return self::read("statistik-penduduk-{$key}.json");
    }

    public static function demografiFirstItemName(string $key): ?string
    {
        $data = self::demografiChartData($key);
        $items = $data['data'] ?? [];

        foreach ($items as $item) {
            $nama = $item['attributes']['nama'] ?? null;
            if ($nama !== null && strtoupper($nama) !== 'TOTAL' && strtoupper($nama) !== 'JUMLAH') {
                return $nama;
            }
        }

        return null;
    }

    public static function kategoriStatistikNames(string $kategori): array
    {
        $data = self::read("kategori-statistik-{$kategori}.json");

        return array_map(fn (array $item) => $item['nama'], $data['data'] ?? []);
    }

    public static function kategoriStatistikPendudukNames(): array
    {
        return self::kategoriStatistikNames('penduduk');
    }

    public static function kategoriStatistikKeluargaNames(): array
    {
        return self::kategoriStatistikNames('keluarga');
    }

    public static function kategoriStatistikRtmNames(): array
    {
        return self::kategoriStatistikNames('rtm');
    }

    public static function kategoriStatistikBantuanNames(): array
    {
        return self::kategoriStatistikNames('bantuan');
    }
}