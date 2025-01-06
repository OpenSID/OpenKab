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
            'permission' => 'profile-kependudukan',
            'submenu' => [
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Penduduk',
                    'url' => 'profile-kependudukan/penduduk',
                    'permission' => 'profile-kependudukan-penduduk',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Data Agama & Adat',
                    'url' => 'profile-kependudukan/agama-adat',
                    'permission' => 'profile-kependudukan-agama-adat',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Kesehatan',
                    'url' => 'profile-kependudukan/kesehatan',
                    'permission' => 'profile-kependudukan-kesehatan',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Pendidikan',
                    'url' => 'profile-kependudukan/pendidikan',
                    'permission' => 'profile-kependudukan-pendidikan',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Ketenagakerjaan',
                    'url' => 'profile-kependudukan/ketenagakerjaan',
                    'permission' => 'profile-kependudukan-ketenagakerjaan',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Jaminan Sosial',
                    'url' => 'profile-kependudukan/jaminan-sosial',
                    'permission' => 'profile-kependudukan-jaminan-sosial',
                ],
                [
                    'icon' => 'far fa-fw fa-circle',
                    'text' => 'Data Papan',
                    'url' => 'profile-kependudukan/papan',
                    'permission' => 'profile-kependudukan-papan',
                ],
                [
                    'text' => 'Penerima Bantuan',
                    'icon' => 'far fa-fw fa-circle',
                    'url' => 'profile-kependudukan/penerima-bantuan',
                    'permission' => 'profile-kependudukan-penerima-bantuan',
                ],
                [
                    'text' => 'Data Suplemen',
                    'icon' => 'fa fafar fa-fw fa-circle',
                    'url' => 'suplemen',
                    'permission' => 'suplemen',
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
            'url' => 'pariwisata',
            'permission' => 'pariwisata',
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
                    'url' => 'pengaturan-opensid/program-bantuan',
                    'permission' => 'pengaturan-opensid-program-bantuan',
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
                    'url' => 'pengaturan-opensid/kategori-artikel/0',
                    'permission' => 'pengaturan-opensid-kategori-artikel',
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
            ],
        ],
    ];
}
