<?php

namespace App\Models\Enums;

class StatistikPendudukEnum extends BaseEnum
{
    public const RENTANG_UMUR = [
        'key' => 13,
        'slug' => 'rentang-umur',
        'label' => 'Rentang Umur',
    ];

    public const KATEGORI_UMUR = [
        'key' => 15,
        'slug' => 'kategori-umur',
        'label' => 'Kategori Umur',
    ];

    public const PENDIDIKAN_KK = [
        'key' => 0,
        'slug' => 'pendidikan-dalam-kk',
        'label' => 'Pendidikan Dalam KK',
    ];

    public const PENDIDIKAN_SEDANG = [
        'key' => 14,
        'slug' => 'pendidikan-sedang-ditempuh',
        'label' => 'Pendidikan Sedang Ditempuh',
    ];

    public const PEKERJAAN = [
        'key' => 1,
        'slug' => 'pekerjaan',
        'label' => 'Pekerjaan',
    ];

    public const STATUS_PERKAWINAN = [
        'key' => 2,
        'slug' => 'status-perkawinan',
        'label' => 'Status Perkawinan',
    ];

    public const AGAMA = [
        'key' => 3,
        'slug' => 'agama',
        'label' => 'Agama',
    ];

    public const JENIS_KELAMIN = [
        'key' => 4,
        'slug' => 'jenis-kelamin',
        'label' => 'Jenis Kelamin',
    ];

    public const HUBUNGAN_KK = [
        'key' => 'hubungan_kk',
        'slug' => 'hubungan-dalam-kk',
        'label' => 'Hubungan Dalam KK',
    ];

    public const WARGA_NEGARA = [
        'key' => 5,
        'slug' => 'warga-negara',
        'label' => 'Warga Negara',
    ];

    public const STATUS_PENDUDUK = [
        'key' => 6,
        'slug' => 'status-penduduk',
        'label' => 'Status Penduduk',
    ];

    public const GOLONGAN_DARAH = [
        'key' => 7,
        'slug' => 'golongan-darah',
        'label' => 'Golongan Darah',
    ];

    public const PENYANDANG_CACAT = [
        'key' => 9,
        'slug' => 'penyandang-cacat',
        'label' => 'Penyandang Cacat',
    ];

    public const PENYAKIT_MENAHUN = [
        'key' => 10,
        'slug' => 'penyakit-menahun',
        'label' => 'Penyakit Menahun',
    ];

    public const AKSEPTOR_KB = [
        'key' => 16,
        'slug' => 'akseptor-kb',
        'label' => 'Akseptor KB',
    ];

    public const AKTA_KELAHIRAN = [
        'key' => 17,
        'slug' => 'akta-kelahiran',
        'label' => 'Akta Kelahiran',
    ];

    public const KEPEMILIKAN_KTP = [
        'key' => 18,
        'slug' => 'kepemilikan-ktp',
        'label' => 'Kepemilikan KTP',
    ];

    public const ASURANSI_KESEHATAN = [
        'key' => 19,
        'slug' => 'asuransi-kesehatan',
        'label' => 'Asuransi Kesehatan',
    ];

    public const STATUS_COVID = [
        'key' => 'covid',
        'slug' => 'status-covid',
        'label' => 'Status Covid',
    ];

    public const SUKU_ETNIS = [
        'key' => 'suku',
        'slug' => 'suku-etnis',
        'label' => 'Suku / Etnis',
    ];

    public const BPJS_KETENAGAKERJAAN = [
        'key' => 'bpjs-tenagakerja',
        'slug' => 'bpjs-ketenagakerjaan',
        'label' => 'BPJS Ketenagakerjaan',
    ];

    public const STATUS_KEHAMILAN = [
        'key' => 'hamil',
        'slug' => 'status-kehamilan',
        'label' => 'Status Kehamilan',
    ];

    public const KEPEMILIKAN_KIA = [
        'key' => 'kia',
        'slug' => 'kepemilikan-kia',
        'label' => 'Kepemilikan KIA',
    ];

    public const KEPEMILIKAN_AKTA_KEMATIAN = [
        'key' => 'akta-kematian',
        'slug' => 'kepemilikan-akta-kematian',
        'label' => 'Kepemilikan Akta Kematian',
    ];

    public static $data = [
        self::RENTANG_UMUR,
        self::KATEGORI_UMUR,
        self::PENDIDIKAN_KK,
        self::PENDIDIKAN_SEDANG,
        self::PEKERJAAN,
        self::STATUS_PERKAWINAN,
        self::AGAMA,
        self::JENIS_KELAMIN,
        self::HUBUNGAN_KK,
        self::WARGA_NEGARA,
        self::STATUS_PENDUDUK,
        self::GOLONGAN_DARAH,
        self::PENYANDANG_CACAT,
        self::PENYAKIT_MENAHUN,
        self::AKSEPTOR_KB,
        self::AKTA_KELAHIRAN,
        self::KEPEMILIKAN_KTP,
        self::ASURANSI_KESEHATAN,
        self::STATUS_COVID,
        self::SUKU_ETNIS,
        self::BPJS_KETENAGAKERJAAN,
        self::STATUS_KEHAMILAN,
        self::KEPEMILIKAN_KIA,
        self::KEPEMILIKAN_AKTA_KEMATIAN,
    ];

    /**
     * Override method all().
     */
    public static function all(): array
    {
        return collect(self::$data)->pluck('label', 'slug')->toArray();
    }

    /**
     * Get all key label.
     */
    public static function allKeyLabel(): array
    {
        return collect(self::$data)->pluck('label', 'key')->toArray();
    }

    /**
     * Get slug from key.
     */
    public static function slugFromKey(mixed $key): ?string
    {
        $item = collect(self::$data)->firstWhere('key', $key);

        return $item ? $item['slug'] : null;
    }

    /**
     * Get key form slug.
     */
    public static function keyFromSlug(mixed $slug): ?string
    {
        $item = collect(self::$data)->firstWhere('slug', $slug);

        return $item ? $item['key'] : null;
    }
}
