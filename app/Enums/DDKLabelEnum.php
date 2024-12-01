<?php

namespace App\Enums;

class DDKLabelEnum
{
    public const STATUS_KEPEMILIKAN_RUMAH = '1.3 Status Kepemilikan Rumah';

    public const SUMBER_AIR_MINUM = '1.4 Sumber Air Minum';

    public const KEPEMILIKAN_LAHAN = '1.5 Kepemilikan Lahan';

    public const PRODUKSI_TAHUN_INI = '1.6 Produksi tahun ini';

    public const KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI = '1 Kepemilikan Jenis Ternak Keluarga Tahun ini';

    public const ALAT_PRODUKSI_BUDIDAYA_IKAN = '1.8 Alat produksi budidaya ikan';

    public const PEMANFAATAN_DANAU_SUNGAI_WADUK_SITU_MATA_AIR_OLEH_KELUARGA = '1.9 Pemanfaatan Danau/Sungai/Waduk/situ/Mata Air oleh Keluarga';

    public const LEMBAGA_PENDIDIKAN_YANG_DIMILIKI_KELUARGA_KOMUNITAS = '1.10 Lembaga Pendidikan Yang Dimiliki Keluarga/Komunitas';

    public const PENGUASAAN_ASET_TANAH_OLEH_KELUARGA = '1.11 Penguasaan Aset Tanah oleh Keluarga';

    public const ASET_SARANA_TRANSPORTASI_UMUM = '1.12 Aset Sarana Transportasi Umum';

    public const ASET_SARANA_PRODUKSI = '1.13 Aset Sarana Produksi';

    public const ASET_PERUMAHAN_DINDING = '1.14 Aset Perumahan (Dinding)';

    public const ASET_PERUMAHAN_LANTAI = '1.14 Aset Perumahan (Lantai)';

    public const ASET_PERUMAHAN_ATAP = '1.14 Aset Perumahan (Atap)';

    public const ASET_LAINNYA = '1.15 Aset Lainnya dalam Keluarga';

    public const KUALITAS_IBU_HAMIL = '1.16 Kualitas Ibu Hamil dalam Keluarga';

    public const KUALITAS_BAYI = '1.17	Kualitas Bayi dalam Keluarga';

    public const KUALITAS_TEMPAT_PERSALINAN = '1.18 Kualitas Persalinan dalam Keluarga (Tempat Persalinan)';

    public const KUALITAS_PERTOLONGAN_PERSALINAN = '1.18 Kualitas Persalinan dalam Keluarga (Pertolongan Persalinan)';

    public const CAKUPAN_IMUNISASI = '1.19 Cakupan Imunisasi';

    public const PENDERITA_SAKIT_DAN_KELAINAN = '1.20 Penderita Sakit dan Kelainan dalam Keluarga';

    public const PERILAKU_HIDUP_BERSIH_SEHAT = '1.21 Perilaku hidup bersih dan sehat dalam Keluarga';

    public const POLA_MAKAN_KELUARGA = '1.22 Pola makan Keluarga';

    public const KEBIASAAN_BEROBAT_BILA_SAKIT = '1.23 Kebiasaan berobat bila sakit dalam keluarga';

    public const STATUS_GIZI_BALITA = '1.24 Status Gizi Balita dalam Keluarga';

    public const JENIS_PENYAKIT_ANGGOTA_KELUARGA = '1.25 Jenis Penyakit yang diderita Anggota Keluarga';

    public const KERUKUNAN = '1.26 Kerukunan';

    public const PERKELAHIAN = '1.27 Perkelahian';

    public const PENCURIAN = '1.28 Pencurian';

    public const PENJARAHAN = '1.29 Penjarahan';

    public const PERJUDIAN = '1.30 Perjudian';

    public const PEMAKAIAN_MIRAS_DAN_NARKOBA = '1.31 Pemakaian Miras dan Narkoba';

    public const PEMBUNUHAN = '1.32 Pembunuhan';

    public const PENCULIKAN = '1.33 Penculikan';

    public const KEJAHATAN_SEKSUAL = '1.34 Kejahatan seksual';

    public const KEKERASAN_DALAM_KELUARGA_ATAU_RUMAH_TANGGA = '1.35 Kekerasan Dalam Keluarga/Rumah Tangga';

    public const MASALAH_KESEJAHTERAAN_KELUARGA = '1.36 Masalah Kesejahteraan Keluarga';

    // ANGGOTA
    public const CACAT_FISIK = '2.2 Cacat Menurut Jenis (Cacat Fisik)';

    public const CACAT_MENTAL = '2.2 Cacat Menurut Jenis (Cacat Mental)';

    public const KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI = '2.3 Kedudukan Anggota Keluarga sebagai Wajib Pajak dan Retribusi';

    public const LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA = '2.4 Lembaga Pemerintahan Yang Diikuti Anggota Keluarga';

    public const LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA = '2.5 Lembaga Kemasyarakatan Yang Diikuti Anggota Keluarga';

    public const LEMBAGA_EKONOMI_YANG_DIIKUTI_ANGGOTA = '2.6 Lembaga Ekonomi Yang Dimiliki Anggota Keluarga';

    public const PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA = '2.7 Produksi bahan galian yang dimiliki anggota keluarga';

    public static function semua(): array
    {
        return array_merge(
            [
                DDKEnum::KODE_STATUS_KEPEMILIKAN_RUMAH => self::STATUS_KEPEMILIKAN_RUMAH,
                DDKEnum::KODE_SUMBER_AIR_MINUM => self::SUMBER_AIR_MINUM,
                DDKEnum::KODE_KEPEMILIKAN_LAHAN => self::KEPEMILIKAN_LAHAN,
                // DDKEnum::KODE_PRODUKSI_TAHUN_INI                          => self::PRODUKSI_TAHUN_INI,
            ],
            array_map(
                static fn ($item): string => self::PRODUKSI_TAHUN_INI.' ('.$item.')',
                DDKEnum::KODE_KATEGORI_PRODUKSI_TAHUN_INI
            ),
            [
                DDKEnum::KODE_KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI => self::KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI,
                DDKEnum::KODE_ALAT_PRODUKSI_BUDIDAYA_IKAN => self::ALAT_PRODUKSI_BUDIDAYA_IKAN,
                DDKEnum::KODE_PEMANFAATAN_DANAU_SUNGAI_WADUK_SITU_MATA_AIR_OLEH_KELUARGA => self::PEMANFAATAN_DANAU_SUNGAI_WADUK_SITU_MATA_AIR_OLEH_KELUARGA,
                DDKEnum::KODE_LEMBAGA_PENDIDIKAN_YANG_DIMILIKI_KELUARGA_KOMUNITAS => self::LEMBAGA_PENDIDIKAN_YANG_DIMILIKI_KELUARGA_KOMUNITAS,
                DDKEnum::KODE_PENGUASAAN_ASET_TANAH_OLEH_KELUARGA => self::PENGUASAAN_ASET_TANAH_OLEH_KELUARGA,
                DDKEnum::KODE_ASET_SARANA_TRANSPORTASI_UMUM => self::ASET_SARANA_TRANSPORTASI_UMUM,
                DDKEnum::KODE_ASET_SARANA_PRODUKSI => self::ASET_SARANA_PRODUKSI,
                DDKEnum::KODE_ASET_PERUMAHAN_LANTAI => self::ASET_PERUMAHAN_LANTAI,
                DDKEnum::KODE_ASET_PERUMAHAN_DINDING => self::ASET_PERUMAHAN_DINDING,
                DDKEnum::KODE_ASET_PERUMAHAN_ATAP => self::ASET_PERUMAHAN_ATAP,
                DDKEnum::KODE_ASET_LAINNYA => self::ASET_LAINNYA,
                DDKEnum::KODE_KUALITAS_IBU_HAMIL => self::KUALITAS_IBU_HAMIL,
                DDKEnum::KODE_KUALITAS_BAYI => self::KUALITAS_BAYI,
                DDKEnum::KODE_KUALITAS_TEMPAT_PERSALINAN => self::KUALITAS_TEMPAT_PERSALINAN,
                DDKEnum::KODE_KUALITAS_PERTOLONGAN_PERSALINAN => self::KUALITAS_PERTOLONGAN_PERSALINAN,
                DDKEnum::KODE_CAKUPAN_IMUNISASI => self::CAKUPAN_IMUNISASI,
                DDKEnum::KODE_PENDERITA_SAKIT_DAN_KELAINAN => self::PENDERITA_SAKIT_DAN_KELAINAN,
                DDKEnum::KODE_PERILAKU_HIDUP_BERSIH_SEHAT => self::PERILAKU_HIDUP_BERSIH_SEHAT,
                DDKEnum::KODE_POLA_MAKAN_KELUARGA => self::POLA_MAKAN_KELUARGA,
                DDKEnum::KODE_KEBIASAAN_BEROBAT_BILA_SAKIT => self::KEBIASAAN_BEROBAT_BILA_SAKIT,
                DDKEnum::KODE_STATUS_GIZI_BALITA => self::STATUS_GIZI_BALITA,
                DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA => self::JENIS_PENYAKIT_ANGGOTA_KELUARGA,
                DDKEnum::KODE_KERUKUNAN => self::KERUKUNAN,
                DDKEnum::KODE_PERKELAHIAN => self::PERKELAHIAN,
                DDKEnum::KODE_PENCURIAN => self::PENCURIAN,
                DDKEnum::KODE_PENJARAHAN => self::PENJARAHAN,
                DDKEnum::KODE_PERJUDIAN => self::PERJUDIAN,
                DDKEnum::KODE_PEMAKAIAN_MIRAS_DAN_NARKOBA => self::PEMAKAIAN_MIRAS_DAN_NARKOBA,
                DDKEnum::KODE_PEMBUNUHAN => self::PEMBUNUHAN,
                DDKEnum::KODE_PENCULIKAN => self::PENCULIKAN,
                DDKEnum::KODE_KEJAHATAN_SEKSUAL => self::KEJAHATAN_SEKSUAL,
                DDKEnum::KODE_KEKERASAN_DALAM_KELUARGA_ATAU_RUMAH_TANGGA => self::KEKERASAN_DALAM_KELUARGA_ATAU_RUMAH_TANGGA,
                DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA => self::MASALAH_KESEJAHTERAAN_KELUARGA,
                //  ANGGOTA
                DDKEnum::KODE_CACAT_FISIK => self::CACAT_FISIK,
                DDKEnum::KODE_CACAT_MENTAL => self::CACAT_MENTAL,
                DDKEnum::KODE_KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI => self::KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI,
                DDKEnum::KODE_LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA => self::LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA,
                DDKEnum::KODE_LEMBAGA_EKONOMI_YANG_DIMILIKI_ANGGOTA => self::LEMBAGA_EKONOMI_YANG_DIIKUTI_ANGGOTA,
                DDKEnum::KODE_LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA => self::LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA,
                DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA => self::PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA,
            ]
        );
    }
}
