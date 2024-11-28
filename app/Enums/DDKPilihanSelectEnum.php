<?php

namespace App\Enums;

class DDKPilihanSelectEnum
{
    public const STATUS_KEPEMILIKAN_RUMAH = [
        1 => 'Milik Sendiri',
        2 => 'Milik Orang Tua',
        3 => 'Milik Keluarga',
        4 => 'Sewa/Kontrak',
        5 => 'Pinjam Pakai',
    ];

    public const PENGUASAAN_ASET_TANAH_OLEH_KELUARGA = [
        1 => 'Tidak memiliki tanah',
        2 => 'Memiliki tanah antara 0,1 - 0,2 ha',
        3 => 'Memiliki tanah antara 0,21 - 0,3 ha',
        4 => 'Memiliki tanah antara 0,31 - 0,4 ha',
        5 => 'Memiliki tanah antara 0,41 - 0,5 ha',
        6 => 'Memiliki tanah antara 0,51 - 0,6 ha',
        7 => 'Memiliki tanah antara 0,61 - 0,7 ha',
        8 => 'Memiliki tanah antara 0,71 - 0,8 ha',
        9 => 'Memiliki tanah antara 0,81 - 0,9 ha',
        10 => 'Memiliki tanah antara 0,91 - 1,0 ha',
        11 => 'Memiliki tanah antara 1,0 - 5,0 ha',
        12 => 'Memilliki tanah lebih dari 5,0 ha',
    ];

    public const PERILAKU_HIDUP_BERSIH_SEHAT = [
        1 => 'Memiliki WC yang permanen/semipermanen',
        2 => 'Memiliki WC yang darurat/kurang memenuhi standar kesehatan',
        3 => 'Biasa buang air besar di sungai/parit/kebun/hutan',
        4 => 'Menggunakan fasilitas MCK umum',
    ];

    public const POLA_MAKAN_KELUARGA = [
        1 => 'Kebiasaan makan dalam sehari 1 kali',
        2 => 'Kebiasaan makan sehari 2 kali',
        3 => 'Kebiasaan makan sehari 3 kali',
        4 => 'Kebiasaan makan sehari lebih dari 3 kali',
        5 => 'Belum tentu sehari makan 1 kali',
    ];

    public const KEBIASAAN_BEROBAT_BILA_SAKIT = [
        1 => 'Dukun Terlatih',
        2 => 'Dokter/puskesmas/mantri kesehatan/perawat/ bidan/posyandu',
        3 => 'Obat tradisional dari dukun pengobatan alternatif',
        4 => 'Paranormal',
        5 => 'Obat tradisional dari keluarga sendiri',
        6 => 'Tidak diobati',
    ];

    public const STATUS_GIZI_BALITA = [
        1 => 'Balita bergizi buruk',
        2 => 'Balita bergizi baik',
        3 => 'Balita bergizi kurang',
        4 => 'Balita bergizi lebih',
    ];

    public const LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA = [
        1 => 'Kepala Desa/Lurah',
        2 => 'Sekretaris Desa/Kelurahan',
        3 => 'Kepala Urusan',
        4 => 'Kepala Dusun/Lingkungan',
        5 => 'Staf Desa/Kelurahan',
        6 => 'Ketua BPD',
        7 => 'Wakil Ketua BPD',
        8 => 'Sekretaris BPD',
        9 => 'Anggota BPD',
    ];

    /**
     * prodeskel_ddk_detail ~ select.
     */
    final public static function semuaSelect(): array
    {
        return [
            DDKEnum::KODE_STATUS_KEPEMILIKAN_RUMAH => self::STATUS_KEPEMILIKAN_RUMAH,
            DDKEnum::KODE_PENGUASAAN_ASET_TANAH_OLEH_KELUARGA => self::PENGUASAAN_ASET_TANAH_OLEH_KELUARGA,
            DDKEnum::KODE_PERILAKU_HIDUP_BERSIH_SEHAT => self::PERILAKU_HIDUP_BERSIH_SEHAT,
            DDKEnum::KODE_POLA_MAKAN_KELUARGA => self::POLA_MAKAN_KELUARGA,
            DDKEnum::KODE_KEBIASAAN_BEROBAT_BILA_SAKIT => self::KEBIASAAN_BEROBAT_BILA_SAKIT,
            DDKEnum::KODE_STATUS_GIZI_BALITA => self::STATUS_GIZI_BALITA,
            // ANGGOTA
            DDKEnum::KODE_LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA => self::LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA,
        ];
    }

    public static function semuaKode(): array
    {
        return [
            DDKEnum::KODE_STATUS_KEPEMILIKAN_RUMAH,
            DDKEnum::KODE_PENGUASAAN_ASET_TANAH_OLEH_KELUARGA,
            DDKEnum::KODE_PERILAKU_HIDUP_BERSIH_SEHAT,
            DDKEnum::KODE_POLA_MAKAN_KELUARGA,
            DDKEnum::KODE_KEBIASAAN_BEROBAT_BILA_SAKIT,
            DDKEnum::KODE_STATUS_GIZI_BALITA,
            // ANGGOTA
            DDKEnum::KODE_LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA,
        ];
    }
}
