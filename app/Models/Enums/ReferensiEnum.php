<?php
namespace App\Models\Enums;

use BenSampo\Enum\Enum;

class ReferensiEnum extends Enum
{
    public const JENIS_KELAMIN                      = 'Jenis Kelamin';
    public const STATUS_HUBUNGAN_DALAM_KELUARGA     = 'Status Hubungan Dalam Keluarga';
    public const STATUS_HUBUNGAN_DALAM_RUMAH_TANGGA = 'Status Hubungan Dalam Rumah Tangga';
    public const AGAMA                              = 'Agama';
    public const PENDIDIKAN_DALAM_KK                = 'Pendidikan Dalam KK';
    public const PENDIDIKAN_SEDANG_DITEMPUH         = 'Pendidikan Sedang Ditempuh';
    public const PEKERJAAN                          = 'Pekerjaan';
    public const STATUS_PERKAWINAN                  = 'Status Perkawinan';
    public const WARGA_NEGARA                       = 'Warga Negara';
    public const GOLONGAN_DARAH                     = 'Golongan Darah';
    public const STATUS_PENDUDUK                    = 'Status Penduduk';
    public const STATUS_DASAR                       = 'Status Dasar';
    public const CACAT                              = 'Cacat';
    public const SAKIT_MENAHUN                      = 'Sakit Menahun';
    public const CARA_KB                            = 'Cara KB';
    public const ASURANSI                           = 'Asuransi';
    public const DUSUN                              = 'Dusun';

    /**
     * Override method all()
     */
    public static function all(): array
    {
        return [
            self::JENIS_KELAMIN                      => 'tweb_penduduk_sex',
            self::STATUS_HUBUNGAN_DALAM_KELUARGA     => 'tweb_penduduk_hubungan',
            self::STATUS_HUBUNGAN_DALAM_RUMAH_TANGGA => 'tweb_rtm_hubungan',
            self::AGAMA                              => 'tweb_penduduk_agama',
            self::PENDIDIKAN_DALAM_KK                => 'tweb_penduduk_pendidikan_kk',
            self::PENDIDIKAN_SEDANG_DITEMPUH         => 'tweb_penduduk_pendidikan',
            self::PEKERJAAN                          => 'tweb_penduduk_pekerjaan',
            self::STATUS_PERKAWINAN                  => 'tweb_penduduk_kawin',
            self::WARGA_NEGARA                       => 'tweb_penduduk_warganegara',
            self::GOLONGAN_DARAH                     => 'tweb_golongan_darah',
            self::STATUS_PENDUDUK                    => 'tweb_penduduk_status',
            self::STATUS_DASAR                       => 'tweb_status_dasar',
            self::CACAT                              => 'tweb_cacat',
            self::SAKIT_MENAHUN                      => 'tweb_sakit_menahun',
            self::CARA_KB                            => 'tweb_cara_kb',
            self::ASURANSI                           => 'tweb_penduduk_asuransi',
            self::DUSUN                              => 'tweb_wil_clusterdesa',
        ];
    }
}
