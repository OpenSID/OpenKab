<?php

namespace App\Enums;

class DDKEnum
{
    // kategori di ProdeskelCustomValue
    public const KATEGORI = 'ddk';

    // kode_value di ProdeskelCustomValue
    public const HASH_ALGO = 'sha256';

    public const HASH_TEMPLATE_DDK = 'ddk_rtf_hash';

    public const HASH_TEMPLATE_DDK_ANGGOTA = 'ddk_anggota_rtf_hash';

    // path dan file
    public const PATH_TEMPLATE = 'Modules'.DIRECTORY_SEPARATOR.'Prodeskel'.DIRECTORY_SEPARATOR.'Storage'.DIRECTORY_SEPARATOR.'template'.DIRECTORY_SEPARATOR;

    public const FILE_TEMPLATE_DDK = 'FormDDK.rtf';

    public const FILE_TEMPLATE_DDK_ANGGOTA = 'FormDDKAnggota.rtf';

    public const KODE_JUMLAH_PENGHASILAN_PERBULAN = 'k1_1'; //#

    public const KODE_JUMLAH_PENGELUARAN_PERBULAN = 'k1_2'; //#

    public const KODE_STATUS_KEPEMILIKAN_RUMAH = 'k1_3'; //select

    public const KODE_SUMBER_AIR_MINUM = 'k1_4'; //checkbox

    public const KODE_SUMBER_AIR_MINUM_CHECKBOX = 'k1_4#'; //#

    public const KODE_KEPEMILIKAN_LAHAN = 'k1_5'; //checkbox

    public const KODE_KEPEMILIKAN_LAHAN_CHECKBOX = 'k1_5#'; //#

    public const KODE_PRODUKSI_TAHUN_INI = 'k1_6';

    public const KODE_KATEGORI_PRODUKSI_TAHUN_INI = [
        'k1_6a' => DDKPilihanProduksiTahunIniEnum::TANAMAN_PANGAN,
        'k1_6b' => DDKPilihanProduksiTahunIniEnum::BUAH_BUAHAN,
        'k1_6c' => DDKPilihanProduksiTahunIniEnum::TANAMAN_OBAT,
        'k1_6d' => DDKPilihanProduksiTahunIniEnum::TANAMAN_PERKEBUNAN,
        'k1_6e' => DDKPilihanProduksiTahunIniEnum::HASIL_HUTAN,
        'k1_6f' => DDKPilihanProduksiTahunIniEnum::PENGOLAHAN_HASIL_TERNAK,
        'k1_6g' => DDKPilihanProduksiTahunIniEnum::PERIKANAN,
    ];

    // table prodeskel_ddk_produksi
    public const KODE_KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI = 'k1_7'; //multi input jumlah

    public const KODE_ALAT_PRODUKSI_BUDIDAYA_IKAN = 'k1_8'; //multi input jumlah

    public const KODE_PEMANFAATAN_DANAU_SUNGAI_WADUK_SITU_MATA_AIR_OLEH_KELUARGA = 'k1_9'; //multiple select

    public const KODE_LEMBAGA_PENDIDIKAN_YANG_DIMILIKI_KELUARGA_KOMUNITAS = 'k1_10'; //multiple select

    public const KODE_PENGUASAAN_ASET_TANAH_OLEH_KELUARGA = 'k1_11'; //select

    public const KODE_ASET_SARANA_TRANSPORTASI_UMUM = 'k1_12'; //multiple select

    public const KODE_ASET_SARANA_PRODUKSI = 'k1_13'; //multiple select

    public const KODE_ASET_PERUMAHAN_DINDING = 'k1_14a'; //multiple select

    public const KODE_ASET_PERUMAHAN_LANTAI = 'k1_14b'; //multiple select

    public const KODE_ASET_PERUMAHAN_ATAP = 'k1_14c'; //multiple select

    public const KODE_ASET_LAINNYA = 'k1_15'; //multiple select

    public const KODE_KUALITAS_IBU_HAMIL = 'k1_16'; //multiple select

    public const KODE_KUALITAS_BAYI = 'k1_17'; //multiple select

    public const KODE_KUALITAS_TEMPAT_PERSALINAN = 'k1_18a'; //multiple select

    public const KODE_KUALITAS_PERTOLONGAN_PERSALINAN = 'k1_18b'; //multi input jumlah

    public const KODE_CAKUPAN_IMUNISASI = 'k1_19'; //multiple select

    public const KODE_PENDERITA_SAKIT_DAN_KELAINAN = 'k1_20'; //multiple select

    public const KODE_PERILAKU_HIDUP_BERSIH_SEHAT = 'k1_21'; //select

    public const KODE_POLA_MAKAN_KELUARGA = 'k1_22'; //select

    public const KODE_KEBIASAAN_BEROBAT_BILA_SAKIT = 'k1_23'; //select

    public const KODE_STATUS_GIZI_BALITA = 'k1_24'; //select

    public const KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA = 'k1_25'; //multiple select

    public const KODE_KERUKUNAN = 'k1_26'; //multiple select

    public const KODE_PERKELAHIAN = 'k1_27'; //multiple select

    public const KODE_PENCURIAN = 'k1_28'; //multiple select

    public const KODE_PENJARAHAN = 'k1_29'; //multiple select

    public const KODE_PERJUDIAN = 'k1_30'; //multiple select

    public const KODE_PEMAKAIAN_MIRAS_DAN_NARKOBA = 'k1_31'; //multiple select

    public const KODE_PEMBUNUHAN = 'k1_32'; //multiple select

    public const KODE_PENCULIKAN = 'k1_33'; //multiple select

    public const KODE_KEJAHATAN_SEKSUAL = 'k1_34'; //multiple select

    public const KODE_KEKERASAN_DALAM_KELUARGA_ATAU_RUMAH_TANGGA = 'k1_35'; //multiple select

    public const KODE_MASALAH_KESEJAHTERAAN_KELUARGA = 'k1_36'; //multiple select

    // ANGGOTA
    public const KODE_NO_URUT = 'k2_1_1'; //#

    public const KODE_NIK = 'k2_1_2'; //#

    public const KODE_NAMA_LENGKAP = 'k2_1_3'; //#

    public const KODE_AKTE_KELAHIRAN = 'k2_1_4'; //#

    public const KODE_JENIS_KELAMIN = 'k2_1_5'; //#

    public const KODE_HUB_KK = 'k2_1_6'; //#

    public const KODE_TEMPAT_LAHIR = 'k2_1_7'; //#

    public const KODE_TANGGAL_LAHIR = 'k2_1_8'; //#

    public const KODE_TANGGAL_PENCATATAN_LAHIR = 'k2_1_9'; //input

    public const KODE_STATUS_PERKAWINAN = 'k2_1_10'; //#

    public const KODE_AGAMA = 'k2_1_11'; //#

    public const KODE_GOL_DARAH = 'k2_1_12'; //#

    public const KODE_KEWARGANEGARAAN = 'k2_1_13'; //#

    public const KODE_PENDIDIKAN = 'k2_1_14'; //#

    public const KODE_PEKERJAAN = 'k2_1_15'; //#

    public const KODE_NAMA_BAPAK_IBU_KANDUNG = 'k2_1_16'; //#

    public const KODE_AKSEPTOR_KB = 'k2_1_17'; //multiple select

    public const KODE_CACAT_FISIK = 'k2_2a'; //multiple select

    public const KODE_CACAT_MENTAL = 'k2_2b'; //multiple select

    public const KODE_KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI = 'k2_3'; //multiple select

    public const KODE_LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA = 'k2_4'; // select

    public const KODE_LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA = 'k2_5'; //multiple select

    public const KODE_LEMBAGA_EKONOMI_YANG_DIMILIKI_ANGGOTA = 'k2_6'; //multiple select

    public const KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA = 'k2_7'; //table prodeskel_ddk_bahan_galian

    /**
     * @return [0 => 'DDK', 1 => 'DDK 1.4 s.d 1.5', 2 => 'DDK 1.6', 3 => 'DDK 1.7 s.d 1.8', 4 => 'DDK 1.18', 5 => 'DDK Anggota', 6 => 'DDK Anggota 2.7', 7 => 'Kode Pilihan',]
     */
    public static function sheetsName(): array
    {
        return [
            0 => 'DDK',
            1 => 'DDK 1.4 s.d 1.5',
            2 => 'DDK 1.6',
            3 => 'DDK 1.7 s.d 1.8',
            4 => 'DDK 1.18',
            5 => 'DDK Anggota',
            6 => 'DDK Anggota 2.7',
            7 => 'Kode Pilihan',
        ];
    }

    /**
     * @description Keluarga + Anggota : kecuali Produksi Keluarga dan Bahan Galian Anggota
     *
     * @mergeOf semuaSelect() + semuaCheckbox() + semuaJumlah() + semuaMultipleSelect() + self::semuaKhususAnggotaTanpaGalian()
     */
    public static function semuaTanpaProduksiDanGalianAgt(): array
    {
        return array_merge(
            self::semuaCheckbox(),
            self::semuaJumlah(),
            self::semuaMultipleSelect(),
            self::semuaSelect(),
            self::semuaKhususAnggotaTanpaGalian(),
        );
    }

    /**
     * @value_type array of values without index
     *
     * @list
     * - KELUARGA
     * 1.3	Status Kepemilikan Rumah
     * 1.11	Penguasaan Aset Tanah oleh Keluarga
     * 1.21	Perilaku hidup bersih dan sehat dalam Keluarga
     * 1.22	Pola makan Keluarga
     * 1.23	Kebiasaan berobat bila sakit dalam keluarga
     * 1.24	Status Gizi Balita dalam Keluarga
     * - ANGGOTA :
     * 2.4	Lembaga Pemerintahan Yang Diikuti Anggota Keluarga
     * */
    public static function semuaSelect(): array
    {
        return DDKPilihanSelectEnum::semuaSelect();
    }

    /**
     * @value_type array of values with index as kode_data
     *
     * @list
     * 1.4 Sumber Air Minum yang digunakan anggota keluarga
     * 1.5 Kepemilikan Lahan
     * */
    public static function semuaCheckbox(): array
    {
        return DDKPilihanCheckboxEnum::semuaCheckbox();
    }

    /**
     * @value_type array of values with index as kode_data
     *
     * @list
     * 1.7 Kepemilikan Jenis Ternak Keluarga Tahun ini
     * 1.8 Alat produksi budidaya ikan
     * (Pertolongan Persalinan) 1.18 Kualitas Persalinan dalam Keluarga (jika ada/pernah ada)
     * */
    public static function semuaJumlah(): array
    {
        return DDKPilihanMultipleJumlahEnum::semuaJumlah();
    }

    /**
     * @value_type array of values without index
     *
     * @list
     * - KELUARGA
     * 1.9 Pemanfaatan Danau/Sungai/Waduk/situ/Mata Air oleh Keluarga
     * 1.10 Lembaga Pendidikan Yang Dimiliki Keluarga/Komunitas
     * 1.12 Aset Sarana Transportasi Umum
     * 1.13 Aset Sarana Produksi
     * 1.14 Aset Perumahan
     * 1.15 Aset Lainnya dalam Keluarga
     * 1.16 Kualitas Ibu Hamil dalam Keluarga (jika ada/pernah ada ibu hamil/nifas)
     * 1.17	Kualitas Bayi dalam Keluarga (jika ada/pernah ada bayi)
     * (Tempat Persalinan) 1.18 Kualitas Persalinan dalam Keluarga (jika ada/pernah ada)
     * 1.19 Cakupan Imunisasi
     * 1.20 Penderita Sakit dan Kelainan dalam Keluarga (jika ada/pernah)
     * 1.25 Jenis Penyakit yang diderita Anggota Keluarga
     * 1.26 Kerukunan
     * 1.27 Perkelahian
     * 1.28 Pencurian
     * 1.29 Penjarahan
     * 1.30 Perjudian
     * 1.31 Pemakaian Miras dan Narkoba
     * 1.32 Pembunuhan
     * 1.33 Penculikan
     * 1.34 Kejahatan seksual
     * 1.35 Kekerasan Dalam Keluarga/Rumah Tangga
     * 1.36 Masalah Kesejahteraan Keluarga
     * - ANGGOTA :
     * 2.1.17	Akseptor KB
     * 2.2	Cacat Menurut Jenis (CACAT FISIK, CACAT MENTAL)
     * 2.3	Kedudukan Anggota Keluarga sebagai Wajib Pajak dan Retribusi
     * 2.5	Lembaga Kemasyarakatan Yang Diikuti Anggota Keluarga
     * 2.6	Lembaga Ekonomi Yang Dimiliki Anggota Keluarga
     * */
    final public static function semuaMultipleSelect(): array
    {
        return DDKPilihanMultipleSelectEnum::semuaMultipleSelect();
    }

    /**
     * - select :
     * 2.4	Lembaga Pemerintahan Yang Diikuti Anggota Keluarga
     * - multipleSelect :
     * 2.1.17	Akseptor KB
     * 2.2	Cacat Menurut Jenis (CACAT FISIK, CACAT MENTAL)
     * 2.3	Kedudukan Anggota Keluarga sebagai Wajib Pajak dan Retribusi
     * 2.5	Lembaga Kemasyarakatan Yang Diikuti Anggota Keluarga
     * 2.6	Lembaga Ekonomi Yang Dimiliki Anggota Keluarga
     * - input
     * self::semuaInputKhususAnggota()
     */
    public static function semuaKhususAnggotaTanpaGalian(): array
    {
        return array_merge(
            [
                self::KODE_AKSEPTOR_KB => DDKPilihanMultipleSelectEnum::AKSEPTOR_KB,
                self::KODE_CACAT_FISIK => DDKPilihanMultipleSelectEnum::CACAT_FISIK,
                self::KODE_CACAT_MENTAL => DDKPilihanMultipleSelectEnum::CACAT_MENTAL,
                self::KODE_KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI => DDKPilihanMultipleSelectEnum::KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI,
                self::KODE_LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA => DDKPilihanSelectEnum::LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA,
                self::KODE_LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA => DDKPilihanMultipleSelectEnum::LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA,
                self::KODE_LEMBAGA_EKONOMI_YANG_DIMILIKI_ANGGOTA => DDKPilihanMultipleSelectEnum::LEMBAGA_EKONOMI_YANG_DIIKUTI_ANGGOTA,
            ],
            self::semuaInputKhususAnggota(),
        );
    }

    /**
     * 2.1.9 Tanggal Pencatatan.
     */
    public static function semuaInputKhususAnggota(): array
    {
        return [self::KODE_TANGGAL_PENCATATAN_LAHIR => ''];
    }

    public static function semuaKodeAnggota(): array
    {
        return array_merge(
            array_keys(self::semuaInputKhususAnggota()),
            [
                self::KODE_LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA,
                self::KODE_AKSEPTOR_KB,
                self::KODE_CACAT_FISIK,
                self::KODE_CACAT_MENTAL,
                self::KODE_KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI,
                self::KODE_LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA,
                self::KODE_LEMBAGA_EKONOMI_YANG_DIMILIKI_ANGGOTA,
                self::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA,
            ],
        );
    }

    final public static function valuesOf(string $kode): array
    {
        foreach (self::semuaTanpaProduksiDanGalianAgt() as $key => $pilihan) {
            if ($kode == $key) {
                return $pilihan;
            }
        }

        return [];
    }
}
