<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Modul extends Enum
{
    const Data = [
        'wilayah',
        'kependudukan',
        'penduduk',
        'dokumen',
        'bantuan',
        'statistik',
        'statistik-penduduk',
        'statistik-keluarga',
        'statistik-rtm',
        'statistik-bantuan',
        'master-data',
        'master-data-bantuan',
        'master-data-artikel',
        'master-data-pengaturan',
        'pengaturan',
        'pengaturan-identitas',
        'pengaturan-users'
    ];

    const Menu = [
        [
            'text' => 'Kependudukan',
            'icon' => 'fa fa-users',
            'rule' => 'kependudukan',
            'submenu' => [
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Penduduk',
                    'url' => 'penduduk',
                    'rule' => 'penduduk'
                ],
            ],
        ],
        [
            'text' => 'Statistik',
            'icon' => 'fas fa-chart-pie',
            'rule' => 'statistik',
            'submenu' => [
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Penduduk',
                    'url' => 'statistik/penduduk',
                    'rule' => 'statistik-penduduk',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Keluarga',
                    'url' => 'statistik/keluarga',
                    'rule' => 'statistik-keluarga',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'RTM',
                    'url' => 'statistik/rtm',
                    'rule' => 'statistik-rtm',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Bantuan',
                    'url' => 'statistik/bantuan',
                    'rule' => 'statistik-bantuan',
                ],

            ],
        ],
        [
            'text' => 'Bantuan',
            'icon' => 'fas fa-handshake',
            'url' => 'bantuan',
            'rule' => 'bantuan',
        ],

        [
            'text' => 'Master Data',
            'icon' => 'fa fa-tags',
            'rule' => 'master-data',
            'submenu' => [
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Bantuan',
                    'url' => 'master/bantuan',
                    'rule' => 'master-data-bantuan',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Kategori Artikel',
                    'url' => 'master/kategori/0',
                    'rule' => 'master-data-artikel',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Pengaturan Aplikasi',
                    'url' => 'master/pengaturan',
                    'rule' => 'master-data-pengaturan',
                ],
            ],
        ],

        [
            'text' => 'Pengaturan',
            'icon' => 'fa fa-cog',
            'rule' => 'pengaturan',
            'submenu' => [
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Identitas',
                    'url' => 'pengaturan/identitas',
                    'rule' => 'pengaturan-identitas',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Pengguna',
                    'url' => 'pengaturan/users',
                    'rule' => 'pengaturan-users',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Group',
                    'url' => 'pengaturan/groups',
                    'rule' => 'pengaturan-group',
                ],
            ],
        ],
    ];

}
