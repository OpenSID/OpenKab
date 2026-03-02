<?php

/*
 *
 * File ini bagian dari:
 *
 * OpenKab
 *
 * Sistem informasi desa sumber terbuka untuk memajukan desa
 *
 * Aplikasi dan source code ini dirilis berdasarkan lisensi GPL V3
 *
 * Hak Cipta 2009 - 2015 Combine Resource Institution (http://lumbungkomunitas.net/)
 * Hak Cipta 2016 - 2025 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 *
 * Dengan ini diberikan izin, secara gratis, kepada siapa pun yang mendapatkan salinan
 * dari perangkat lunak ini dan file dokumentasi terkait ("Aplikasi Ini"), untuk diperlakukan
 * tanpa batasan, termasuk hak untuk menggunakan, menyalin, mengubah dan/atau mendistribusikan,
 * asal tunduk pada syarat berikut:
 *
 * Pemberitahuan hak cipta di atas dan pemberitahuan izin ini harus disertakan dalam
 * setiap salinan atau bagian penting Aplikasi Ini. Barang siapa yang menghapus atau menghilangkan
 * pemberitahuan ini melanggar ketentuan lisensi Aplikasi Ini.
 *
 * PERANGKAT LUNAK INI DISEDIAKAN "SEBAGAIMANA ADANYA", TANPA JAMINAN APA PUN, BAIK TERSURAT MAUPUN
 * TERSIRAT. PENULIS ATAU PEMEGANG HAK CIPTA SAMA SEKALI TIDAK BERTANGGUNG JAWAB ATAS KLAIM, KERUSAKAN ATAU
 * KEWAJIBAN APAPUN ATAS PENGGUNAAN ATAU LAINNYA TERKAIT APLIKASI INI.
 *
 * @package   OpenKab
 * @author    Tim Pengembang OpenKab
 * @copyright Hak Cipta 2009 - 2015 Combine Resource Institution (http://lumbungkomunitas.net/)
 * @copyright Hak Cipta 2016 - 2025 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 * @link      https://github.com/OpenKab/OpenKab
 *
 */

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Status kelengkapan data presisi.
 */
final class StatusKelengkapanPresisiEnum extends Enum
{
    public const TIDAK_LENGKAP = 0;
    public const LENGKAP_SEBAGIAN = 1;
    public const DATA_LENGKAP = 2;

    public static function getDescription($value): string
    {
        return match ($value) {
            self::TIDAK_LENGKAP => 'Tidak Lengkap',
            self::LENGKAP_SEBAGIAN => 'Lengkap Sebagian',
            self::DATA_LENGKAP => 'Data Lengkap',
            default => 'Status Tidak Diketahui',
        };
    }

    public static function getBadgeClass($value): string
    {
        return match ($value) {
            self::DATA_LENGKAP => 'label-success',
            self::LENGKAP_SEBAGIAN => 'label-warning',
            self::TIDAK_LENGKAP => 'label-danger',
            default => 'label-default',
        };
    }

    public static function getAll(): array
    {
        return [
            self::TIDAK_LENGKAP => 'Tidak Lengkap',
            self::LENGKAP_SEBAGIAN => 'Lengkap Sebagian',
            self::DATA_LENGKAP => 'Data Lengkap',
        ];
    }
}