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
            'text' => 'Profile Kependudukan',
            'icon' => 'fa fa-users',
            'permission' => 'datapokok',
            'submenu' => [
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Penduduk',
                    'url' => 'penduduk',
                    'permission' => 'penduduk',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Data Agama & Adat',
                    'url' => 'data-pokok/agama-adat',
                    'permission' => 'datapokok-agama-adat',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Kesehatan',
                    'url' => 'data-pokok/kesehatan',
                    'permission' => 'datapokok-kesehatan',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Pendidikan',
                    'url' => 'data-pokok/pendidikan',
                    'permission' => 'datapokok-pendidikan',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Ketenagakerjaan',
                    'url' => 'data-pokok/ketenagakerjaan',
                    'permission' => 'datapokok-ketenagakerjaan',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Jaminan Sosial',
                    'url' => 'data-pokok/jaminan-sosial',
                    'permission' => 'datapokok-jaminan-sosial',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Data Papan',
                    'url' => 'satu-data/dtks/papan',
                    'permission' => 'datapokok-papan',
                ],
                [
                    'text' => 'Penerima Bantuan',
                    'icon' => 'far fa-fw fa-circle',
                    'url' => 'bantuan',
                    'permission' => 'bantuan',
                ],
            ],
        ],
        [
            'text' => 'Statistik Kependudukan',
            'icon' => 'fas fa-chart-pie',
            'permission' => 'statistik',
            'submenu' => [
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Penduduk',
                    'url' => 'statistik/penduduk',
                    'permission' => 'statistik-penduduk',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Keluarga',
                    'url' => 'statistik/keluarga',
                    'permission' => 'statistik-keluarga',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'RTM',
                    'url' => 'statistik/rtm',
                    'permission' => 'statistik-rtm',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Bantuan',
                    'url' => 'statistik/bantuan',
                    'permission' => 'statistik-bantuan',
                ],

            ],
        ],
        [
            'icon' => 'fa fa-users',
            'text' => 'Pariwisata',
            'url' => 'data-pokok/pariwisata',
            'permission' => 'datapokok-pariwisata',
        ],
        [
            'text' => 'SOTK',
            'icon' => 'fa fa-tags',
            'permission' => 'organisasi',
            'submenu' => [
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Departemen',
                    'url' => 'departments',
                    'permission' => 'organisasi-departemen',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Jabatan',
                    'url' => 'positions',
                    'permission' => 'organisasi-position',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Pejabat Daerah',
                    'url' => 'employees',
                    'permission' => 'organisasi-employee',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Struktur Bagan',
                    'url' => 'orgchart',
                    'permission' => 'organisasi-chart',
                ],
            ],
        ],
        [
            'text' => 'Pengaturan OpenSID',
            'icon' => 'fa fa-tags',
            'permission' => 'pengaturan-opensid',
            'submenu' => [
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Program Bantuan',
                    'url' => 'master/bantuan',
                    'permission' => 'master-data-bantuan',
                ],
                [
                    'text' => 'Data Suplemen',
                    'icon' => 'far fa-fw fa-circle',
                    'url' => 'suplemen',
                    'permission' => 'suplemen',
                ],

                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Kategori Artikel',
                    'url' => 'master/kategori/0',
                    'permission' => 'master-data-artikel',
                ],
            ],
        ],
        [
            'text' => 'Pengaturan Peta',
            'icon' => 'fa fa-map',
            'permission' => 'pengaturan-peta',
            'submenu' => [
                [
                    'text' => 'Tipe Lokasi',
                    'icon' => 'far fa-fw fa-circle',
                    'url' => 'point',
                    'permission' => 'point',
                ],
            ],
        ],
        [
            'text' => 'Pengaturan Web',
            'icon' => 'fa fa-globe',
            'permission' => 'website',
            'submenu' => [
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Menu Website',
                    'url' => 'cms/menus',
                    'permission' => 'website-menu',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Halaman',
                    'url' => 'cms/pages',
                    'permission' => 'website-pages',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Artikel',
                    'url' => 'cms/articles',
                    'permission' => 'website-article',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Kategori Artikel',
                    'url' => 'cms/categories',
                    'permission' => 'website-categories',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Slider',
                    'url' => 'cms/slides',
                    'permission' => 'website-slider',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Daftar Unduhan',
                    'url' => 'cms/downloads',
                    'permission' => 'website-downloads',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Statistik Pengunjung',
                    'url' => 'cms/statistik',
                    'permission' => 'website-statistik',
                ],
            ],
        ],
        [
            'text' => 'Pengaturan Pengguna',
            'icon' => 'fa fa-globe',
            'permission' => 'website',
            'submenu' => [
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Pengguna',
                    'url' => 'pengaturan/users',
                    'permission' => 'pengaturan-users',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Grup',
                    'url' => 'pengaturan/groups',
                    'permission' => 'pengaturan-group',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Riwayat Pengguna',
                    'url' => 'pengaturan/activities',
                    'permission' => 'pengaturan-activities',
                ],
            ],
        ],
        [
            'text' => 'Pengaturan Aplikasi',
            'icon' => 'fa fa-cog',
            'permission' => 'pengaturan',
            'submenu' => [
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Identitas',
                    'url' => 'pengaturan/identitas',
                    'permission' => 'pengaturan-identitas',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Aplikasi',
                    'url' => 'pengaturan/settings',
                    'permission' => 'pengaturan-settings',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'OpenDK',
                    'url' => 'pengaturan/opendk',
                    'permission' => 'pengaturan-opendk',
                ],
            ],
        ],
    ];
}
