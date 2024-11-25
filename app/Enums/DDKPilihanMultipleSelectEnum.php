<?php

namespace App\Enums;

class DDKPilihanMultipleSelectEnum
{
    public const PEMANFAATAN_DANAU_SUNGAI_WADUK_SITU_MATA_AIR_OLEH_KELUARGA = [
        1 => 'Usaha Perikanan',
        2 => 'Air minum/air baku',
        3 => 'Cuci dan mandi',
        4 => 'Irigasi',
        5 => 'Buang air besar',
        6 => 'Pembangkit listrik',
        7 => 'Prasarana transportasi',
        8 => 'Sumber air panas',
    ];

    public const LEMBAGA_PENDIDIKAN_YANG_DIMILIKI_KELUARGA_KOMUNITAS = [
        1 => 'TK/Preschool/Play Group',
        2 => 'SD/Sederajat',
        3 => 'SMP/Sederajat',
        4 => 'SMA/Sederajat',
        5 => 'Perguruan Tinggi',
        6 => 'Pondok Pesantren',
        7 => "Taman Pendidikan Alqur'an",
        8 => 'Rhaudatul Athfal (Tk)',
        9 => 'Madrasah Ibtidaiyah',
        10 => 'Madrasah Tsanawiyah',
        11 => 'Madrasah Aliyah',
        12 => 'Sekolah Tinggi Agama Islam',
        13 => 'Universitas Swasta Islam',
        14 => 'Seminari Menengah',
        15 => 'Seminari Tinggi',
        16 => 'Biara',
        17 => 'Perguruan Tinggi Swasta Katolik',
        18 => 'Sekolah Dasar Swasta Katolik',
        19 => 'SLTP Swasta Katolik',
        20 => 'SLTA Swasta Katolik',
        21 => 'Lembaga Kursus Keterampilan Swasta Katolik',
        22 => 'Lembaga Pendidikan Swasta Kristen Protestan',
        23 => 'Lembaga Pendidikan Swasta Hindu',
        24 => 'Lembaga Pendidikan Swasta Budha',
        25 => 'Lembaga Pendidikan Swasta Konghucu',
        26 => 'Kursus Bahasa',
        27 => 'Kursus Menjahit',
        28 => 'Kursus Montir',
        29 => 'Kursus Komputer',
        30 => 'Kursus Mengemudi',
        31 => 'Kursus Satpam',
        32 => 'Kursus Bela Diri',
    ];

    public const ASET_SARANA_TRANSPORTASI_UMUM = [
        1 => 'Memiliki ojek motor/sepeda motor/bentor',
        2 => 'Memiliki becak',
        3 => 'Memiiki cidemo/andong/dokar',
        4 => 'Memiliki perahu tidak bermotor',
        5 => 'Memiliki tongkang',
        6 => 'Memiliki bus penumpang/angkutan orang/barang',
        7 => 'Memiliki sepeda dayung',
        8 => 'Memiliki bajaj/kancil',
    ];

    public const ASET_SARANA_PRODUKSI = [
        1 => 'Memiliki penggilingan padi',
        2 => 'Memiliki traktor',
        3 => 'Memiliki pabrik pengolahan hasil pertanian',
        4 => 'Memiliki kapal penangkap ikan',
        5 => 'Memiliki alat pengolahan hasil perikanan',
        6 => 'Memiliki alat pengolahan hasil peternakan',
        7 => 'Memiliki alat pengolahan hasil perkebunan',
        8 => 'Memiliki alat pengolahan hasil hutan',
        9 => 'Memiliki alat produksi dan pengolah hasil pertambangan',
        10 => 'Memiliki alat produksi dan pengolah hasil Industri kerajinan keluarga skala kecil dan menengah',
        11 => 'Memiliki alat produksi dan pengolahan hasil industri bahan bakar dan gas skala rumah tangga',
    ];

    public const ASET_PERUMAHAN_DINDING = [
        1 => 'Tembok',
        2 => 'Kayu',
        3 => 'Bambu',
        4 => 'Tanah liat',
        5 => 'Pelepah kelapa/lontar/gebang',
        6 => 'Dedaunan',
    ];

    public const ASET_PERUMAHAN_LANTAI = [
        1 => 'Keramik',
        2 => 'Semen',
        3 => 'Kayu',
        4 => 'Tanah',
    ];

    public const ASET_PERUMAHAN_ATAP = [
        1 => 'Genteng',
        2 => 'Seng',
        3 => 'Asbes',
        4 => 'Beton',
        5 => 'Bambu',
        6 => 'Kayu',
        7 => 'Daun lontar/gebang/enau',
        8 => 'Daun ilalang',
    ];

    public const ASET_LAINNYA = [
        1 => 'Memiliki TV dan elektronik sejenis lainnya',
        2 => 'Memiliki sepeda motor pribadi',
        3 => 'Memiliki mobil pribadi dan sejenisnya',
        4 => 'Memiliki perahu bermotor',
        5 => 'Memiliki kapal barang',
        6 => 'Memiliki kapal penumpang',
        7 => 'Memiliki kapal pesiar',
        8 => 'Memiliki/menyewa helikopter pribadi',
        9 => 'Memiliki/menyewa pesawat terbang pribadi',
        10 => 'Memiliki ternak besar',
        11 => 'Memiliki ternak kecil',
        12 => 'Memiliki hiasan emas/berlian',
        13 => 'Memiliki buku tabungan bank',
        14 => 'Memiliki buku surat berharga',
        15 => 'Memiliki sertifikat deposito',
        16 => 'Memiliki sertifikat tanah',
        17 => 'Memiliki sertifikat bangunan',
        18 => 'Memiliki perusahaan industri besar',
        19 => 'Memiliki perusahaan industri menengah',
        20 => 'Memiliki perusahaan industri kecil',
        21 => 'Memiliki usaha perikanan',
        22 => 'Memiliki usaha peternakan',
        23 => 'Memiliki usaha perkebunan',
        24 => 'Memiliki usaha pasar swalayan',
        25 => 'Memiliki usaha di pasar swalayan',
        26 => 'Memiliki usaha di pasar tradisional',
        27 => 'Memiliki usaha di pasar desa',
        28 => 'Memiliki usaha transportasi',
        29 => 'Memiliki saham di perusahaan',
        30 => 'Pelanggan Telkom',
        31 => 'Memiliki HP GSM',
        32 => 'Memiliki HP CDMA',
        33 => 'Memiliki Usaha Wartel',
        34 => 'Memiliki parabola',
        35 => 'Berlangganan koran/majalah',
    ];

    public const KUALITAS_IBU_HAMIL = [
        1 => 'Ibu hamil periksa di Posyandu',
        2 => 'Ibu hamil periksa di Puskesmas',
        3 => 'Ibu hamil periksa di Rumah Sakit',
        4 => 'Ibu hamil periksa di Dokter Praktek',
        5 => 'Ibu hamil periksa di Bidan Praktek',
        6 => 'Ibu hamil periksa di Dukun Terlatih',
        7 => 'Ibu hamil tidak periksa kesehatan',
        8 => 'Ibu hamil yang meninggal',
        9 => 'Ibu hamil melahirkan',
        10 => 'Ibu nifas sakit',
        11 => 'Kematian ibu nifas',
        12 => 'Ibu nifas sehat',
        13 => 'Kematian ibu saat melahirkan',
    ];

    public const KUALITAS_BAYI = [
        1 => 'Keguguran kandungan',
        2 => 'Bayi lahir hidup normal',
        3 => 'Bayi lahir hidup cacat',
        4 => 'Bayi lahir mati',
        5 => 'Bayi lahir berat kurang dari 2,5 kg',
        6 => 'Bayi lahir berat lebih dari 4 kg',
        7 => 'Bayi 0-5 tahun hidup yang menderita kelainan organ tubuh, fisik dan mental',
    ];

    public const KUALITAS_TEMPAT_PERSALINAN = [
        1 => 'Tempat persalinan Rumah Sakit Umum',
        2 => 'Tempat persalinan Rumah Bersalin',
        3 => 'Tempat persalinan Puskesmas',
        4 => 'Tempat persalinan Polindes',
        5 => 'Tempat persalinan Balai Kesehatan Ibu Anak',
        6 => 'Tempat persalinan rumah praktek bidan',
        7 => 'Tempat praktek dokter',
        8 => 'Rumah dukun',
        9 => 'Rumah sendiri',
    ];

    public const CAKUPAN_IMUNISASI = [
        1 => 'DPT-1',
        2 => 'BCG',
        3 => 'Polio -1',
        4 => 'DPT-2',
        5 => 'Polio-2',
        6 => 'Polio-3',
        7 => 'DPT-3',
        8 => 'Campak',
        9 => 'Cacar',
        10 => 'Sudah Semua',
    ];

    public const PENDERITA_SAKIT_DAN_KELAINAN = [
        1 => 'Muntaber',
        2 => 'Demam Berdarah',
        3 => 'Kolera',
        4 => 'Polio',
        5 => 'Cikungunya',
        6 => 'Flu Burung',
        7 => 'Busung Lapar',
        8 => 'Kelaparan',
        9 => 'Kulit Bersisik',
        10 => 'Kelainan fisik',
        11 => 'Kelainan mental',
    ];

    public const JENIS_PENYAKIT_ANGGOTA_KELUARGA = [
        1 => 'Jantung',
        2 => 'Lever',
        3 => 'Paru-paru',
        4 => 'Kanker',
        5 => 'Stroke',
        6 => 'Diabetes Melitus',
        7 => 'Ginjal',
        8 => 'Malaria',
        9 => 'Lepra/Kusta',
        10 => 'HIV/AIDS',
        11 => 'Gila/stress',
        12 => 'TBC',
        13 => 'Asma',
    ];

    public const KERUKUNAN = [
        1 => 'Korban luka dalam keluarga akibat konflik Sara',
        2 => 'Korban meninggal dalam keluarga akibat konflik Sara',
        3 => 'Janda/duda dalam keluarga akibat konflik Sara',
        4 => 'Anak yatim/piatu dalam keluarga akibat konflik Sara',
    ];

    public const PERKELAHIAN = [
        1 => 'Korban jiwa akibat perkelahian dalam keluarga',
        2 => 'Korban luka parah akibat perkelahian dalam keluarga',
    ];

    public const PENCURIAN = [
        1 => 'Korban pencurian, perampokan dalam keluarga',
    ];

    public const PENJARAHAN = [
        1 => 'Korban penjarahan yang pelakunya anggota keluarga',
        2 => 'Korban penjarahan yang pelakunya bukan anggota keluarga',
    ];

    public const PERJUDIAN = [
        1 => 'Anggota keluarga yang memiliki kebiasaan berjudi',
    ];

    public const PEMAKAIAN_MIRAS_DAN_NARKOBA = [
        1 => 'Anggota keluarga mengkonsumsi Miras yang dilarang',
        2 => 'Anggota keluarga yang mengkonsumsi Narkoba',
    ];

    public const PEMBUNUHAN = [
        1 => 'Korban pembunuhan dalam keluarga yang pelakunya anggota keluarga',
        2 => 'Korban pembunuhan dalam keluarga yang pelakunya bukan anggota keluarga',
    ];

    public const PENCULIKAN = [
        1 => 'Korban penculikan yang pelakunya anggota keluarga',
        2 => 'Korban penculikan yang pelakunya bukan anggota keluarga',
    ];

    public const KEJAHATAN_SEKSUAL = [
        1 => 'Korban perkosaan/pelecehan seksual yang pelakunya anggota keluarga',
        2 => 'Korban perkosaan/pelecehan seksual yang pelakunya bukan anggota keluarga',
        3 => 'Korban kehamilan di luar nikah yang sah menurut hukum adat',
        4 => 'Korban kehamilan yang tidak dinikahi pelakunya',
        5 => 'Korban kehamilan yang tidak/belum disahkan secara hukum agama dan hukum negara',
    ];

    public const KEKERASAN_DALAM_KELUARGA_ATAU_RUMAH_TANGGA = [
        1 => 'Adanya pertengkaran dalam keluarga antara anak dan orang tua',
        2 => 'Adanya pertengkaran dalam keluarga antara anak dan anak',
        3 => 'Adanya pertengkaran dalam keluarga antara ayah dan ibu/orang tua',
        4 => 'Adanya pertengkaran dalam keluarga antara anak dan pembantu',
        5 => 'Adanya pertengkaran dalam keluarga antara anak dan anggota keluarga lain',
        6 => 'Adanya pemukulan/tindakan fisik antara anak dengan orang tua',
        7 => 'Adanya pemukulan/tindakan fisik antara orang tua dengan anak',
        8 => 'Adanya pemukulan/tindakan fisik antara anak dengan anggota keluarga lain',
        9 => 'Adanya pemukulan/tindakan fisik antara orang tua dengan orang tua',
        10 => 'Adanya pemukulan/tindakan fisik antara anak dengan pembantu',
        11 => 'Adanya pemukulan/tindakan fisik antara orang tua dengan pembantu',
    ];

    public const MASALAH_KESEJAHTERAAN_KELUARGA = [
        1 => 'Ada anggota keluarga yang mengemis',
        2 => 'Ada anggota keluarga yang bermalam/tidur di jalanan/emperan toko/ kolong jembatan',
        3 => 'Ada anggota keluarga yang termasuk manusia lanjut usia (di atas 60 thn)',
        4 => 'Ada anak anggota keluarga yang mengemis',
        5 => 'Ada anak dan anggota keluarga yang menjadi pengamen',
        6 => 'Ada anggota keluarga yang gila/stres',
        7 => 'Ada anggota keluarga yang cacat fisik',
        8 => 'Ada anggota keluarga yang cacat mental',
        9 => 'Ada anggota keluarga yang kelainan kulit',
        10 => 'Ada anggota keluarga yang menjadi pengamen',
        11 => 'Anggota keluarga yatim/piatu',
        12 => 'Keluarga janda',
        13 => 'Keluarga duda',
        14 => 'Tinggal di bantaran sungai',
        15 => 'Tinggal di jalur hijau',
        16 => 'Tinggal di kawasan jalur rel kereta api',
        17 => 'Tinggal di kawasan jalur sutet',
        18 => 'Tinggal di kawasan kumuh dan padat pemukiman',
        19 => 'Ada anggota keluarga yang menganggur',
        20 => 'Ada anak yang membantu orang tua mendapatkan penghasilan',
        21 => 'Kepala keluarga perempuan',
        22 => 'Ada anggota keluarga eks narapidana',
        23 => 'Tinggal di desa/kelurahan rawan banjir',
        24 => 'Tinggal di daerah rawan bencana tsunami',
        25 => 'Tinggal di desa/kelurahan rawan gunung meletus',
        26 => 'Tinggal di jalur rawan gempa bumi',
        27 => 'Tinggal di kawasan rawan tanah longsor',
        28 => 'Tinggal di kawasan rawan kebakaran',
        29 => 'Tinggal di desa/kelurahan rawan kelaparan',
        30 => 'Tinggal di desa/kelurahan rawan air bersih',
        31 => 'Tinggal di desa/kelurahan rawan kekeringan',
        32 => 'Tinggal di desa/kelurahan rawan gagal tanam/panen',
        33 => 'Tinggal di daerah kawasan kering, tandus & kritis',
    ];

    // ANGGOTA
    public const AKSEPTOR_KB = [
        1 => 'Pil',
        2 => 'Spiral',
        3 => 'Suntik',
        4 => 'Susuk',
        5 => 'Kondom',
        6 => 'Vasektomi',
        7 => 'Tubektomi',
    ];

    public const CACAT_FISIK = [
        1 => 'Tuna rungu',
        2 => 'Tuna wicara',
        3 => 'Tuna netra',
        4 => 'Lumpuh',
        5 => 'Sumbing',
    ];

    public const CACAT_MENTAL = [
        1 => 'Idiot',
        2 => 'Gila',
        3 => 'Stress',
    ];

    public const KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI = [
        1 => 'Wajib Pajak Bumi dan Bangunan',
        2 => 'Wajib Pajak Penghasilan Perorangan',
        3 => 'Wajib Pajak Badan/Perusahaan',
        4 => 'Wajib Pajak Kendaraan Bermotor',
        5 => 'Wajib Retribusi Kebersihan',
        6 => 'Wajib Retribusi Keamanan',
        7 => 'Wajib iuran',
        8 => 'Wajib pungutan',
    ];

    public const LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA = [
        1 => 'Pengurus RT',
        2 => 'Anggota Pengurus RT',
        3 => 'Pengurus RW',
        4 => 'Anggota Pengurus RW',
        5 => 'Pengurus LKMD/K/LPM',
        6 => 'Anggota LKMD/K/LPM',
        7 => 'Pengurus PKK',
        8 => 'Anggota PKK',
        9 => 'Pengurus Lembaga Adat',
        10 => 'Pengurus Karang Taruna',
        11 => 'Anggota Karang Taruna',
        12 => 'Pengurus Hansip/Linmas',
        13 => 'Pengurus Poskamling',
        14 => 'Pengurus Organisasi Perempuan',
        15 => 'Anggota Organisasi Perempuan',
        16 => 'Pengurus Organisasi Bapak-bapak',
        17 => 'Anggota Organisasi Bapak-bapak',
        18 => 'Pengurus Organisasi keagamaan',
        19 => 'Anggota Organisasi keagamaan',
        20 => 'Pengurus Organisasi profesi wartawan',
        21 => 'Anggota Organisasi profesi wartawan',
        22 => 'Pengurus Posyandu',
        23 => 'Pengurus Posyantekdes',
        24 => 'Pengurus Organisasi Kelompok Tani/Nelayan',
        25 => 'Anggota Organisasi Kelompok Tani/Nelayan',
        26 => 'Pengurus Lembaga Gotong royong',
        27 => 'Anggota Lembaga Gotong royong',
        28 => 'Pengurus Organisasi Profesi guru',
        29 => 'Anggota Organisasi Profesi guru',
        30 => 'Pengurus Organisasi profesi dokter/tenaga medis',
        31 => 'Anggota Organisasi profesi/tenaga medis',
        32 => 'Pengurus organisasi pensiunan',
        33 => 'Anggota organisasi pensiunan',
        34 => 'Pengurus organisasi pemirsa/pendengar',
        35 => 'Anggota organisasi pemirsa/pendengar',
        36 => 'Pengurus lembaga pencinta alam',
        37 => 'Anggota organisasi pencinta alam',
        38 => 'Pengurus organisasi pengembangan ilmu pengetahuan',
        39 => 'Anggota organisasi pengembangan ilmu pengetaahuan',
        40 => 'Pemilik yayasan',
        41 => 'Pengurus yayasan',
        42 => 'Anggota yayasan',
        43 => 'Pengurus Satgas Kebersihan',
        44 => 'Anggota Satgas Kebersihan',
        45 => 'Pengurus Satgas Kebakaran',
        46 => 'Anggota Satgas Kebakaran',
        47 => 'Pengurus Posko Penanggulangan Bencana',
        48 => 'Anggota Tim Penanggulangan Bencana',
    ];

    public const LEMBAGA_EKONOMI_YANG_DIIKUTI_ANGGOTA = [
        1 => 'Koperasi',
        2 => 'Unit Usaha Simpan Pinjam',
        3 => 'Industri Kerajinan Tangan',
        4 => 'Industri Pakaian',
        5 => 'Industri Usaha Makanan',
        6 => 'Industri Alat Rumah Tangga',
        7 => 'Industri Usaha Bahan Bangunan',
        8 => 'Industri Alat Pertanian',
        9 => 'Restoran',
        10 => 'Toko/ Swalayan',
        11 => 'Warung Kelontongan/Kios',
        12 => 'Angkutan Darat',
        13 => 'Angkutan Sungai',
        14 => 'Angkutan Laut',
        15 => 'Angkutan Udara',
        16 => 'Jasa Ekspedisi/Pengiriman Barang',
        17 => 'Tukang Sumur',
        18 => 'Usaha Pasar Harian',
        19 => 'Usaha Pasar Mingguan',
        20 => 'Usaha Pasar Ternak',
        21 => 'Usaha Pasar Hasil Bumi Dan Tambang',
        22 => 'Usaha Perdagangan Antar Pulau',
        23 => 'Pengijon',
        24 => 'Pedagang Pengumpul/Tengkulak',
        25 => 'Usaha Peternakan',
        26 => 'Usaha Perikanan',
        27 => 'Usaha Perkebunan',
        28 => 'Kelompok Simpan Pinjam',
        29 => 'Usaha Minuman',
        30 => 'Industri Farmasi',
        31 => 'Industri Karoseri',
        32 => 'Penitipan Kendaraan Bermotor',
        33 => 'Industri Perakitan Elektronik',
        34 => 'Pengolahan Kayu',
        35 => 'Bioskop',
        36 => 'Film Keliling',
        37 => 'Sandiwara/Drama',
        38 => 'Group Lawak',
        39 => 'Jaipongan',
        40 => 'Wayang Orang/Golek',
        41 => 'Group Musik/Band',
        42 => 'Group Vokal/Paduan Suara',
        43 => 'Usaha Persewaan Tenaga Listrik',
        44 => 'Usaha Pengecer Gas Dan Bahan Bakar Minyak',
        45 => 'Usaha Air Minum Dalam Kemasan',
        46 => 'Tukang Kayu',
        47 => 'Tukang Batu',
        48 => 'Tukang Jahit/Bordir',
        49 => 'Tukang Cukur',
        50 => 'Tukang Service Elektronik',
        51 => 'Tukang Besi',
        52 => 'Tukang Pijat/Urut',
        53 => 'Tukang Sumur',
        54 => 'Notaris',
        55 => 'Pengacara/Advokat',
        56 => 'Konsultan Manajemen',
        57 => 'Konsultan Teknis',
        58 => 'Pejabat Pembuat Akta Tanah',
        59 => 'Losmen',
        60 => 'Wisma',
        61 => 'Asrama',
        62 => 'Persewaan Kamar',
        63 => 'Kontrakan Rumah',
        64 => 'Mess',
        65 => 'Hotel',
        66 => 'Home Stay',
        67 => 'Villa',
        68 => 'Town House',
        69 => 'Usaha Asuransi',
        70 => 'Lembaga Keuangan Bukan Bank',
        71 => 'Lembaga Perkreditan Rakyat',
        72 => 'Pegadaian',
        73 => 'Bank Perkreditan Rakyat',
        74 => 'Usaha Penyewaan Alat Pesta',
        75 => 'Usaha Pengolahan dan Penjualan Hasil Hutan',
    ];

    /**
     * prodeskel_ddk_detail ~ select multiple.
     */
    final public static function semuaMultipleSelect(): array
    {
        return [
            DDKEnum::KODE_PEMANFAATAN_DANAU_SUNGAI_WADUK_SITU_MATA_AIR_OLEH_KELUARGA => self::PEMANFAATAN_DANAU_SUNGAI_WADUK_SITU_MATA_AIR_OLEH_KELUARGA,
            DDKEnum::KODE_LEMBAGA_PENDIDIKAN_YANG_DIMILIKI_KELUARGA_KOMUNITAS => self::LEMBAGA_PENDIDIKAN_YANG_DIMILIKI_KELUARGA_KOMUNITAS,
            DDKEnum::KODE_ASET_SARANA_TRANSPORTASI_UMUM => self::ASET_SARANA_TRANSPORTASI_UMUM,
            DDKEnum::KODE_ASET_SARANA_PRODUKSI => self::ASET_SARANA_PRODUKSI,
            DDKEnum::KODE_ASET_PERUMAHAN_LANTAI => self::ASET_PERUMAHAN_LANTAI,
            DDKEnum::KODE_ASET_PERUMAHAN_DINDING => self::ASET_PERUMAHAN_DINDING,
            DDKEnum::KODE_ASET_PERUMAHAN_ATAP => self::ASET_PERUMAHAN_ATAP,
            DDKEnum::KODE_ASET_LAINNYA => self::ASET_LAINNYA,
            DDKEnum::KODE_KUALITAS_IBU_HAMIL => self::KUALITAS_IBU_HAMIL,
            DDKEnum::KODE_KUALITAS_BAYI => self::KUALITAS_BAYI,
            DDKEnum::KODE_KUALITAS_TEMPAT_PERSALINAN => self::KUALITAS_TEMPAT_PERSALINAN,
            DDKEnum::KODE_CAKUPAN_IMUNISASI => self::CAKUPAN_IMUNISASI,
            DDKEnum::KODE_PENDERITA_SAKIT_DAN_KELAINAN => self::PENDERITA_SAKIT_DAN_KELAINAN,
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
            DDKEnum::KODE_AKSEPTOR_KB => self::AKSEPTOR_KB,
            DDKEnum::KODE_CACAT_FISIK => self::CACAT_FISIK,
            DDKEnum::KODE_CACAT_MENTAL => self::CACAT_MENTAL,
            DDKEnum::KODE_KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI => self::KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI,
            DDKEnum::KODE_LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA => self::LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA,
            DDKEnum::KODE_LEMBAGA_EKONOMI_YANG_DIMILIKI_ANGGOTA => self::LEMBAGA_EKONOMI_YANG_DIIKUTI_ANGGOTA,
        ];
    }

    public static function semuaKode(): array
    {
        return [
            DDKEnum::KODE_PEMANFAATAN_DANAU_SUNGAI_WADUK_SITU_MATA_AIR_OLEH_KELUARGA,
            DDKEnum::KODE_LEMBAGA_PENDIDIKAN_YANG_DIMILIKI_KELUARGA_KOMUNITAS,
            DDKEnum::KODE_ASET_SARANA_TRANSPORTASI_UMUM,
            DDKEnum::KODE_ASET_SARANA_PRODUKSI,
            DDKEnum::KODE_ASET_PERUMAHAN_LANTAI,
            DDKEnum::KODE_ASET_PERUMAHAN_DINDING,
            DDKEnum::KODE_ASET_PERUMAHAN_ATAP,
            DDKEnum::KODE_ASET_LAINNYA,
            DDKEnum::KODE_KUALITAS_IBU_HAMIL,
            DDKEnum::KODE_KUALITAS_BAYI,
            DDKEnum::KODE_KUALITAS_TEMPAT_PERSALINAN,
            DDKEnum::KODE_CAKUPAN_IMUNISASI,
            DDKEnum::KODE_PENDERITA_SAKIT_DAN_KELAINAN,
            DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA,
            DDKEnum::KODE_KERUKUNAN,
            DDKEnum::KODE_PERKELAHIAN,
            DDKEnum::KODE_PENCURIAN,
            DDKEnum::KODE_PENJARAHAN,
            DDKEnum::KODE_PERJUDIAN,
            DDKEnum::KODE_PEMAKAIAN_MIRAS_DAN_NARKOBA,
            DDKEnum::KODE_PEMBUNUHAN,
            DDKEnum::KODE_PENCULIKAN,
            DDKEnum::KODE_KEJAHATAN_SEKSUAL,
            DDKEnum::KODE_KEKERASAN_DALAM_KELUARGA_ATAU_RUMAH_TANGGA,
            DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA,
            //  ANGGOTA,
            DDKEnum::KODE_AKSEPTOR_KB,
            DDKEnum::KODE_CACAT_FISIK,
            DDKEnum::KODE_CACAT_MENTAL,
            DDKEnum::KODE_KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI,
            DDKEnum::KODE_LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA,
            DDKEnum::KODE_LEMBAGA_EKONOMI_YANG_DIMILIKI_ANGGOTA,
        ];
    }
}
