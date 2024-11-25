<?php

namespace App\Enums;

class DDKPilihanProduksiTahunIniEnum
{
    public const TANAMAN_PANGAN = 'Tanaman Pangan';

    public const BUAH_BUAHAN = 'Buah-Buahan';

    public const TANAMAN_OBAT = 'Tanaman Obat';

    public const TANAMAN_PERKEBUNAN = 'Tanaman Perkebunan';

    public const HASIL_HUTAN = 'Hasil Hutan';

    public const PENGOLAHAN_HASIL_TERNAK = 'Pengolahan Hasil Ternak';

    public const PERIKANAN = 'Perikanan';

    public const PENGATURAN = [
        self::TANAMAN_PANGAN => [
            'jumlah_pohon' => false,
            'luas_panen' => true,
        ],
        self::BUAH_BUAHAN => [
            'jumlah_pohon' => true,
            'luas_panen' => true,
        ],
        self::TANAMAN_OBAT => [
            'jumlah_pohon' => false,
            'luas_panen' => true,
        ],
        self::TANAMAN_PERKEBUNAN => [
            'jumlah_pohon' => true,
            'luas_panen' => true,
        ],
        self::HASIL_HUTAN => [
            'jumlah_pohon' => false,
            'luas_panen' => false,
        ],
        self::PENGOLAHAN_HASIL_TERNAK => [
            'jumlah_pohon' => false,
            'luas_panen' => false,
        ],
        self::PERIKANAN => [
            'jumlah_pohon' => false,
            'luas_panen' => false,
        ],
    ];

    /**
     * [kategori => ['komoditas' => string, 'satuan' => string] ].
     */
    public const DATA = [
        self::TANAMAN_PANGAN => [
            ['komoditas' => 'Jagung',            'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kacang kedelai',    'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kacang tanah',      'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kacang panjang',    'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kacang mede',       'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kacang merah',      'satuan' => 'Kg/Th'],
            ['komoditas' => 'Padi sawah',        'satuan' => 'Kg/Th'],
            ['komoditas' => 'Padi ladang',       'satuan' => 'Kg/Th'],
            ['komoditas' => 'Ubi kayu',          'satuan' => 'Kg/Th'],
            ['komoditas' => 'Ubi jalar',         'satuan' => 'Kg/Th'],
            ['komoditas' => 'Cabe',              'satuan' => 'Kg/Th'],
            ['komoditas' => 'Bawah merah',       'satuan' => 'Kg/Th'],
            ['komoditas' => 'Bawang putih',      'satuan' => 'Kg/Th'],
            ['komoditas' => 'Tomat',             'satuan' => 'Kg/Th'],
            ['komoditas' => 'Sawi',              'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kentang',           'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kubis',             'satuan' => 'Kg/Th'],
            ['komoditas' => 'Mentimun',          'satuan' => 'Kg/Th'],
            ['komoditas' => 'Buncis',            'satuan' => 'Kg/Th'],
            ['komoditas' => 'Brocoli',           'satuan' => 'Kg/Th'],
            ['komoditas' => 'Terong',            'satuan' => 'Kg/Th'],
            ['komoditas' => 'Bayam',             'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kangkung',          'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kacang turis',      'satuan' => 'Kg/Th'],
            ['komoditas' => 'Umbi-umbian lain',  'satuan' => 'Kg/Th'],
            ['komoditas' => 'Selada',            'satuan' => 'Kg/Th'],
            ['komoditas' => 'Talas',             'satuan' => 'Kg/Th'],
            ['komoditas' => 'Wortel',            'satuan' => 'Kg/Th'],
        ],
        self::BUAH_BUAHAN => [
            ['komoditas' => 'Jeruk',         'satuan' => 'Kg/Th'],
            ['komoditas' => 'Alpokat',       'satuan' => 'Kg/Th'],
            ['komoditas' => 'Mangga',        'satuan' => 'Kg/Th'],
            ['komoditas' => 'Rambutan',      'satuan' => 'Kg/Th'],
            ['komoditas' => 'Manggis',       'satuan' => 'Kg/Th'],
            ['komoditas' => 'Salak',         'satuan' => 'Kg/Th'],
            ['komoditas' => 'Apel',          'satuan' => 'Kg/Th'],
            ['komoditas' => 'Pepaya',        'satuan' => 'Kg/Th'],
            ['komoditas' => 'Belimbing',     'satuan' => 'Kg/Th'],
            ['komoditas' => 'Durian',        'satuan' => 'Kg/Th'],
            ['komoditas' => 'Sawo',          'satuan' => 'Kg/Th'],
            ['komoditas' => 'Duku',          'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kokosan',       'satuan' => 'Kg/Th'],
            ['komoditas' => 'Pisang',        'satuan' => 'Kg/Th'],
            ['komoditas' => 'Markisa',       'satuan' => 'Kg/Th'],
            ['komoditas' => 'Lengkeng',      'satuan' => 'Kg/Th'],
            ['komoditas' => 'Semangka',      'satuan' => 'Kg/Th'],
            ['komoditas' => 'Limau',         'satuan' => 'Kg/Th'],
            ['komoditas' => 'Jeruk nipis',   'satuan' => 'Kg/Th'],
            ['komoditas' => 'Sirsak',        'satuan' => 'Kg/Th'],
            ['komoditas' => 'Melon',         'satuan' => 'Kg/Th'],
            ['komoditas' => 'Jambu air',     'satuan' => 'Kg/Th'],
            ['komoditas' => 'Nangka',        'satuan' => 'Kg/Th'],
            ['komoditas' => 'Sirsak',        'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kelapa',        'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kedondong',     'satuan' => 'Kg/Th'],
            ['komoditas' => 'Anggur',        'satuan' => 'Kg/Th'],
            ['komoditas' => 'Nenas',         'satuan' => 'Kg/Th'],
            ['komoditas' => 'Jambu klutuk',  'satuan' => 'Kg/Th'],
            ['komoditas' => 'Murbei',        'satuan' => 'Kg/Th'],
        ],
        self::TANAMAN_OBAT => [
            ['komoditas' => 'Jahe',          'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kunyit',        'satuan' => 'Kg/Th'],
            ['komoditas' => 'Lengkuas',      'satuan' => 'Kg/Th'],
            ['komoditas' => 'Mengkudu',      'satuan' => 'Kg/Th'],
            ['komoditas' => 'Daun dewa',     'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kumis kucing',  'satuan' => 'Kg/Th'],
            ['komoditas' => 'Buah Merah',    'satuan' => 'Kg/Th'],
            ['komoditas' => 'Sambiloto',     'satuan' => 'Kg/Th'],
            ['komoditas' => 'Temulawak',     'satuan' => 'Kg/Th'],
            ['komoditas' => 'Temu hitam',    'satuan' => 'Kg/Th'],
            ['komoditas' => 'Temu putih',    'satuan' => 'Kg/Th'],
            ['komoditas' => 'Temu putri',    'satuan' => 'Kg/Th'],
            ['komoditas' => 'Temu kunci',    'satuan' => 'Kg/Th'],
            ['komoditas' => 'Daun sirih',    'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kayu manis',    'satuan' => 'Kg/Th'],
            ['komoditas' => 'Daun sereh',    'satuan' => 'Kg/Th'],
            ['komoditas' => 'Mahkota dewa',  'satuan' => 'Kg/Th'],
            ['komoditas' => 'Akar wangi',    'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kencur',        'satuan' => 'Kg/Th'],
            ['komoditas' => 'Jamur',         'satuan' => 'Kg/Th'],
        ],
        self::TANAMAN_PERKEBUNAN => [
            ['komoditas' => 'Kelapa',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kelapa sawit',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kopi',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Cengkeh',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Coklat',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Pinang',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Lada',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Karet',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Jambu mete',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Tembakau',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Pala',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Vanili',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Jarak pagar',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Jarak kepyar',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Tebu',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kapuk',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Kemiri',				'satuan' => 'Kg/Th'],
            ['komoditas' => 'Teh',				'satuan' => 'Kg/Th'],
        ],
        self::HASIL_HUTAN => [
            ['komoditas' => 'Kayu bakar',             'satuan' => 'M3/Th'],
            ['komoditas' => 'Madu lebah',             'satuan' => 'Liter/Th'],
            ['komoditas' => 'Rotan',                  'satuan' => 'Kg/Th'],
            ['komoditas' => 'Damar',                  'satuan' => 'Kg/Th'],
            ['komoditas' => 'Bambu',                  'satuan' => 'M3/Th'],
            ['komoditas' => 'Jati',                   'satuan' => 'M3/Th'],
            ['komoditas' => 'Nilam',                  'satuan' => 'Liter/Th'],
            ['komoditas' => 'Nira Lontar',            'satuan' => 'Liter/Th'],
            ['komoditas' => 'Tepung Sagu',            'satuan' => 'Kg/Th'],
            ['komoditas' => 'Ijuk Enau',              'satuan' => 'Kg/Th'],
            ['komoditas' => 'Mahoni',                 'satuan' => 'M3/Th'],
            ['komoditas' => 'Cemara',                 'satuan' => 'M3/Th'],
            ['komoditas' => 'Kayu cendana',           'satuan' => 'M3/Th'],
            ['komoditas' => 'Kayu gaharu',            'satuan' => 'M3/Th'],
            ['komoditas' => 'Sarang burung',          'satuan' => 'Kg/Th'],
            ['komoditas' => 'Meranti',                'satuan' => 'M3/Th'],
            ['komoditas' => 'Kayu besi',              'satuan' => 'M3/Th'],
            ['komoditas' => 'Kayu ulin',              'satuan' => 'M3/Th'],
            ['komoditas' => 'Kemenyan',               'satuan' => 'Kg/Th'],
            ['komoditas' => 'Gambir',                 'satuan' => 'Kg/Th'],
            ['komoditas' => 'Minyak kayu putih',      'satuan' => 'Liter/Th'],
            ['komoditas' => 'Gula Lontar',            'satuan' => 'Kg/Th'],
            ['komoditas' => 'Gula Enau',              'satuan' => 'Kg/Th'],
        ],
        self::PENGOLAHAN_HASIL_TERNAK => [
            ['komoditas' => 'Susu',                   'satuan' => 'kg/th'],
            ['komoditas' => 'Kulit',                  'satuan' => 'M/th'],
            ['komoditas' => 'Telur',                  'satuan' => 'kg/th'],
            ['komoditas' => 'Daging',                 'satuan' => 'kg/th'],
            ['komoditas' => 'Madu Lebah',             'satuan' => 'liter/th'],
            ['komoditas' => 'Bulu',                   'satuan' => 'kg/th'],
            ['komoditas' => 'Air liur burung walet',  'satuan' => 'kg/thn'],
            ['komoditas' => 'Minyak hewani',          'satuan' => 'liter/thn'],
            ['komoditas' => 'Hiasan/lukisan',         'satuan' => 'unit/thn'],
            ['komoditas' => 'Kerajinan Cinderamata',  'satuan' => 'jenis/thn'],
            ['komoditas' => 'Dendeng',                'satuan' => 'kg/th'],
            ['komoditas' => 'Abon',                   'satuan' => 'kg/th'],
            ['komoditas' => 'Biogas',                 'satuan' => 'kg/th'],
            ['komoditas' => 'Telur Asin',             'satuan' => 'kg/th'],
            ['komoditas' => 'Kerupuk Kulit',          'satuan' => 'kg/th'],
        ],
        self::PERIKANAN => [
            ['komoditas' => 'Tuna',             'satuan' => 'kg/th'],
            ['komoditas' => 'Salmon',           'satuan' => 'kg/th'],
            ['komoditas' => 'Tongkol/cakalang', 'satuan' => 'kg/th'],
            ['komoditas' => 'Hiu',              'satuan' => 'kg/th'],
            ['komoditas' => 'Kakap',            'satuan' => 'kg/th'],
            ['komoditas' => 'Tenggiri',         'satuan' => 'kg/th'],
            ['komoditas' => 'Jambal',           'satuan' => 'kg/th'],
            ['komoditas' => 'Pari',             'satuan' => 'kg/th'],
            ['komoditas' => 'Kuwe',             'satuan' => 'kg/th'],
            ['komoditas' => 'Belanak',          'satuan' => 'kg/th'],
            ['komoditas' => 'Cumi',             'satuan' => 'kg/th'],
            ['komoditas' => 'Gurita',           'satuan' => 'kg/th'],
            ['komoditas' => 'Sarden',           'satuan' => 'kg/th'],
            ['komoditas' => 'Bawal',            'satuan' => 'kg/th'],
            ['komoditas' => 'Baronang',         'satuan' => 'kg/th'],
            ['komoditas' => 'Kembung',          'satuan' => 'kg/th'],
            ['komoditas' => 'Balanak',          'satuan' => 'kg/th'],
            ['komoditas' => 'Ikan ekor kuning', 'satuan' => 'kg/th'],
            ['komoditas' => 'Kerapu/Sunuk',     'satuan' => 'kg/th'],
            ['komoditas' => 'Teripang',         'satuan' => 'kg/th'],
            ['komoditas' => 'Barabara',         'satuan' => 'kg/th'],
            ['komoditas' => 'Cucut',            'satuan' => 'kg/th'],
            ['komoditas' => 'Layur',            'satuan' => 'kg/th'],
            ['komoditas' => 'Ayam-ayam',        'satuan' => 'kg/th'],
            ['komoditas' => 'Udang/lobster',    'satuan' => 'kg/th'],
            ['komoditas' => 'Tembang',          'satuan' => 'kg/th'],
            ['komoditas' => 'Bandeng',          'satuan' => 'kg/th'],
            ['komoditas' => 'Nener',            'satuan' => 'kg/th'],
            ['komoditas' => 'Kerang',           'satuan' => 'kg/th'],
            ['komoditas' => 'Kepiting',         'satuan' => 'kg/th'],
            ['komoditas' => 'Mas',              'satuan' => 'kg/th'],
            ['komoditas' => 'Rajungan',         'satuan' => 'kg/th'],
            ['komoditas' => 'Mujair',           'satuan' => 'kg/th'],
            ['komoditas' => 'Lele',             'satuan' => 'kg/th'],
            ['komoditas' => 'Gabus',            'satuan' => 'kg/th'],
            ['komoditas' => 'Patin',            'satuan' => 'kg/th'],
            ['komoditas' => 'Nila',             'satuan' => 'kg/th'],
            ['komoditas' => 'Sepat',            'satuan' => 'kg/th'],
            ['komoditas' => 'Gurame',           'satuan' => 'kg/th'],
            ['komoditas' => 'Belut',            'satuan' => 'kg/th'],
            ['komoditas' => 'Penyu',            'satuan' => 'kg/th'],
            ['komoditas' => 'Rumput laut',      'satuan' => 'kg/th'],
            ['komoditas' => 'Kodok',            'satuan' => 'kg/th'],
            ['komoditas' => 'Katak',            'satuan' => 'kg/th'],
        ],
    ];

    /**
     * prodeskel_ddk_produksi ~ [kategori_komoditas => ['kode' => prefix_komoditas + kode, 'komoditas' => string, 'satuan' => string] ].
     *
     * @param mixed|null $data
     */
    final public static function dataWithKode($data = null): array
    {
        $data = $data == null ? self::DATA : $data;
        $tmp = [];

        foreach ($data as $key_kategori => $komoditas) {
            $kode_komoditas = 1;
            $tmp[$key_kategori] = array_map(static function ($item) use ($key_kategori, &$kode_komoditas): array {
                return array_merge($item, [
                    'kode' => array_search($key_kategori, DDKEnum::KODE_KATEGORI_PRODUKSI_TAHUN_INI).'_'.$kode_komoditas++,
                ]);
            }, $komoditas);
        }

        return $tmp;
    }
}
