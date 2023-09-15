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
                ]
            ],
        ],

        [
            'text' => 'Organisasi',
            'icon' => 'fa fa-tags',
            'role' => 'organisasi',
            'submenu' => [
                [
                    'icon' => 'fa fa-building',
                    'text' => 'Departemen',
                    'url' => 'departments',
                    'role' => 'organisasi-departemen',
                ],
                [
                    'icon' => 'fa fa-star',
                    'text' => 'Jabatan',
                    'url' => 'positions',
                    'role' => 'organisasi-position',
                ],
                [
                    'icon' => 'fa fa-users',
                    'text' => 'Pejabat Daerah',
                    'url' => 'employees',
                    'role' => 'organisasi-employee',
                ],
                [
                    'icon' => 'fa fa-sitemap',
                    'text' => 'Struktur Bagan',
                    'url' => 'orgchart',
                    'role' => 'organisasi-chart',
                ],
            ],
        ],
        [
            'text' => 'Modul Web',
            'icon' => 'fa fa-globe',
            'role' => 'website',
            'submenu' => [
                [
                    'icon' => 'fas fa-bars',
                    'text' => 'Menu Website',
                    'url' => 'cms/menus',
                    'role' => 'website-menu',
                ],
                [
                    'icon' => 'fas fa-file-text',
                    'text' => 'Halaman',
                    'url' => 'cms/pages',
                    'role' => 'website-pages',
                ],
                [
                    'icon' => 'fas fa-list',
                    'text' => 'Artikel',
                    'url' => 'cms/articles',
                    'role' => 'website-article',
                ],
                [
                    'icon' => 'fas fa-folder',
                    'text' => 'Kategori Artikel',
                    'url' => 'cms/categories',
                    'role' => 'website-categories',
                ],
                [
                    'icon' => 'fas fa-image',
                    'text' => 'Slider',
                    'url' => 'cms/slides',
                    'role' => 'website-slider',
                ],
                [
                    'icon' => 'fas fa-download',
                    'text' => 'Download Area',
                    'url' => 'cms/downloads',
                    'role' => 'website-downloads',
                ],
            ],
        ],
        [
            'text' => 'Pengaturan',
            'icon' => 'fa fa-cog',
            'role' => 'pengaturan',
            'submenu' => [
                [
                    'icon' => 'fas fa-cogs',
                    'text' => 'Identitas',
                    'url' => 'pengaturan/identitas',
                    'role' => 'pengaturan-identitas',
                ],
                [
                    'icon' => 'fas fa-user',
                    'text' => 'Pengguna',
                    'url' => 'pengaturan/users',
                    'role' => 'pengaturan-users',
                ],
                [
                    'icon' => 'fas fa-users',
                    'text' => 'Grup',
                    'url' => 'pengaturan/groups',
                    'role' => 'pengaturan-group',
                ],
                [
                    'icon' => 'fas fa-history',
                    'text' => 'Riwayat Pengguna',
                    'url' => 'pengaturan/activities',
                    'role' => 'pengaturan-users',
                ],
                [
                    'icon' => 'fas fa-gear',
                    'text' => 'Aplikasi',
                    'url' => 'pengaturan/settings',
                    'role' => 'pengaturan-settings',
                ],
            ],
        ],

    ];
}
