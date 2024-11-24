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
            'permission' => 'kependudukan',
            'submenu' => [
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Penduduk',
                    'url' => 'penduduk',
                    'permission' => 'penduduk',
                ],
            ],
        ],
        [
            'text' => 'Statistik',
            'icon' => 'fas fa-chart-pie',
            'permission' => 'statistik',
            'submenu' => [
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Penduduk',
                    'url' => 'statistik/penduduk',
                    'permission' => 'statistik-penduduk',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Keluarga',
                    'url' => 'statistik/keluarga',
                    'permission' => 'statistik-keluarga',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'RTM',
                    'url' => 'statistik/rtm',
                    'permission' => 'statistik-rtm',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Bantuan',
                    'url' => 'statistik/bantuan',
                    'permission' => 'statistik-bantuan',
                ],

            ],
        ],
        [
            'text' => 'Bantuan',
            'icon' => 'fas fa-handshake',
            'url' => 'bantuan',
            'permission' => 'bantuan',
        ],
        [
            'text' => 'Data Suplemen',
            'icon' => 'fa fa-users',
            'url' => 'suplemen',
            'permission' => 'suplemen',
        ],

        [
            'text' => 'Master Data',
            'icon' => 'fa fa-tags',
            'permission' => 'master-data',
            'submenu' => [
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Bantuan',
                    'url' => 'master/bantuan',
                    'permission' => 'master-data-bantuan',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Kategori Artikel',
                    'url' => 'master/kategori/0',
                    'permission' => 'master-data-artikel',
                ],
            ],
        ],

        [
            'text' => 'Organisasi',
            'icon' => 'fa fa-tags',
            'permission' => 'organisasi',
            'submenu' => [
                [
                    'icon' => 'fa fa-building',
                    'text' => 'Departemen',
                    'url' => 'departments',
                    'permission' => 'organisasi-departemen',
                ],
                [
                    'icon' => 'fa fa-star',
                    'text' => 'Jabatan',
                    'url' => 'positions',
                    'permission' => 'organisasi-position',
                ],
                [
                    'icon' => 'fa fa-users',
                    'text' => 'Pejabat Daerah',
                    'url' => 'employees',
                    'permission' => 'organisasi-employee',
                ],
                [
                    'icon' => 'fa fa-sitemap',
                    'text' => 'Struktur Bagan',
                    'url' => 'orgchart',
                    'permission' => 'organisasi-chart',
                ],
            ],
        ],
        [
            'text' => 'Modul Web',
            'icon' => 'fa fa-globe',
            'permission' => 'website',
            'submenu' => [
                [
                    'icon' => 'fas fa-bars',
                    'text' => 'Menu Website',
                    'url' => 'cms/menus',
                    'permission' => 'website-menu',
                ],
                [
                    'icon' => 'fas fa-file-text',
                    'text' => 'Halaman',
                    'url' => 'cms/pages',
                    'permission' => 'website-pages',
                ],
                [
                    'icon' => 'fas fa-list',
                    'text' => 'Artikel',
                    'url' => 'cms/articles',
                    'permission' => 'website-article',
                ],
                [
                    'icon' => 'fas fa-folder',
                    'text' => 'Kategori Artikel',
                    'url' => 'cms/categories',
                    'permission' => 'website-categories',
                ],
                [
                    'icon' => 'fas fa-image',
                    'text' => 'Slider',
                    'url' => 'cms/slides',
                    'permission' => 'website-slider',
                ],
                [
                    'icon' => 'fas fa-download',
                    'text' => 'Daftar Unduhan',
                    'url' => 'cms/downloads',
                    'permission' => 'website-downloads',
                ],
                [
                    'icon' => 'fas fa-chart-line',
                    'text' => 'Statistik Pengunjung',
                    'url' => 'cms/statistik',
                    'permission' => 'website-statistik',
                ],
            ],
        ],
        [
            'text' => 'Pengaturan',
            'icon' => 'fa fa-cog',
            'permission' => 'pengaturan',
            'submenu' => [
                [
                    'icon' => 'fas fa-cogs',
                    'text' => 'Identitas',
                    'url' => 'pengaturan/identitas',
                    'permission' => 'pengaturan-identitas',
                ],
                [
                    'icon' => 'fas fa-user',
                    'text' => 'Pengguna',
                    'url' => 'pengaturan/users',
                    'permission' => 'pengaturan-users',
                ],
                [
                    'icon' => 'fas fa-users',
                    'text' => 'Grup',
                    'url' => 'pengaturan/groups',
                    'permission' => 'pengaturan-group',
                ],
                [
                    'icon' => 'fas fa-history',
                    'text' => 'Riwayat Pengguna',
                    'url' => 'pengaturan/activities',
                    'permission' => 'pengaturan-activities',
                ],
                [
                    'icon' => 'fas fa-gear',
                    'text' => 'Aplikasi',
                    'url' => 'pengaturan/settings',
                    'permission' => 'pengaturan-settings',
                ],
            ],
        ],

    ];
}
