<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Modul extends Enum
{
    const permision = [
        'read', 'write', 'edit', 'delete',
    ];

    const Menu = [
        [
            'text' => 'Kependudukan',
            'icon' => 'fa fa-users',
            'role' => 'kependudukan',
            'submenu' => [
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Penduduk',
                    'url' => 'penduduk',
                    'role' => 'penduduk',
                ],
            ],
        ],
        [
            'text' => 'Statistik',
            'icon' => 'fas fa-chart-pie',
            'role' => 'statistik',
            'submenu' => [
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Penduduk',
                    'url' => 'statistik/penduduk',
                    'role' => 'statistik-penduduk',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Keluarga',
                    'url' => 'statistik/keluarga',
                    'role' => 'statistik-keluarga',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'RTM',
                    'url' => 'statistik/rtm',
                    'role' => 'statistik-rtm',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Bantuan',
                    'url' => 'statistik/bantuan',
                    'role' => 'statistik-bantuan',
                ],

            ],
        ],
        [
            'text' => 'Bantuan',
            'icon' => 'fas fa-handshake',
            'url' => 'bantuan',
            'role' => 'bantuan',
        ],

        [
            'text' => 'Master Data',
            'icon' => 'fa fa-tags',
            'role' => 'master-data',
            'submenu' => [
                [
                    'icon' => 'fa fa-building',
                    'text' => 'Departemen',
                    'url' => 'departments',
                    'role' => '',
                ],
                [
                    'icon' => 'fa fa-chart-bar',
                    'text' => 'Jabatan',
                    'url' => 'positions',
                    'role' => '',
                ],
                [
                    'icon' => 'fa fa-users',
                    'text' => 'Pegawai',
                    'url' => 'employees',
                    'role' => '',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Bantuan',
                    'url' => 'master/bantuan',
                    'role' => 'master-data-bantuan',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Kategori Artikel',
                    'url' => 'master/kategori/0',
                    'role' => 'master-data-artikel',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Pengaturan Aplikasi',
                    'url' => 'master/pengaturan',
                    'role' => 'master-data-pengaturan',
                ],
            ],
        ],

        [
            'text' => 'Pengaturan',
            'icon' => 'fa fa-cog',
            'role' => 'pengaturan',
            'submenu' => [
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Identitas',
                    'url' => 'pengaturan/identitas',
                    'role' => 'pengaturan-identitas',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Pengguna',
                    'url' => 'pengaturan/users',
                    'role' => 'pengaturan-users',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Grup',
                    'url' => 'pengaturan/groups',
                    'role' => 'pengaturan-group',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Riwayat Pengguna',
                    'url' => 'pengaturan/activities',
                    'role' => 'pengaturan-users',
                ],
            ],
        ],
    ];
}
