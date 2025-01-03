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
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Penduduk',
                    'url' => 'profile-kependudukan/penduduk',
                    'permission' => 'profile-kependudukan-penduduk',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Data Agama & Adat',
                    'url' => 'profile-kependudukan/agama-adat',
                    'permission' => 'profile-kependudukan-agama-adat',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Kesehatan',
                    'url' => 'profile-kependudukan/kesehatan',
                    'permission' => 'profile-kependudukan-kesehatan',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Pendidikan',
                    'url' => 'profile-kependudukan/pendidikan',
                    'permission' => 'profile-kependudukan-pendidikan',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Ketenagakerjaan',
                    'url' => 'profile-kependudukan/ketenagakerjaan',
                    'permission' => 'profile-kependudukan-ketenagakerjaan',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Jaminan Sosial',
                    'url' => 'profile-kependudukan/jaminan-sosial',
                    'permission' => 'profile-kependudukan-jaminan-sosial',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Data Papan',
                    'url' => 'profile-kependudukan/papan',
                    'permission' => 'profile-kependudukan-papan',
                ],
                [
                    'text' => 'Penerima Bantuan',
                    'icon' => 'fas fa-angle-right',
                    'url' => 'profile-kependudukan/penerima-bantuan',
                    'permission' => 'profile-kependudukan-penerima-bantuan',
                ],
                [
                    'text' => 'Data Suplemen',
                    'icon' => 'fa fafas fa-angle-right',
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
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Departemen',
                    'url' => 'departments',
                    'permission' => 'organisasi-departemen',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Jabatan',
                    'url' => 'positions',
                    'permission' => 'organisasi-position',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Pejabat Daerah',
                    'url' => 'employees',
                    'permission' => 'organisasi-employee',
                ],
                [
                    'icon' => 'fas fa-angle-right',
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
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Program Bantuan',
                    'url' => 'pengaturan-opensid/program-bantuan',
                    'permission' => 'pengaturan-opensid-program-bantuan',
                ],
                [
                    'text' => 'Data Suplemen',
                    'icon' => 'fas fa-angle-right',
                    'url' => 'suplemen',
                    'permission' => 'suplemen',
                ],

                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Kategori Artikel',
                    'url' => 'pengaturan-opensid/kategori-artikel/0',
                    'permission' => 'pengaturan-opensid-kategori-artikel',
                ],
            ],
        ],
        [
            'text' => 'Kategori Artikel',
            'icon' => 'fa fa-globe',
            'permission' => 'website',
            'submenu' => [
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Menu Website',
                    'url' => 'cms/menus',
                    'permission' => 'website-menu',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Halaman',
                    'url' => 'cms/pages',
                    'permission' => 'website-pages',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Artikel',
                    'url' => 'cms/articles',
                    'permission' => 'website-article',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Kategori Artikel',
                    'url' => 'cms/categories',
                    'permission' => 'website-categories',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Slider',
                    'url' => 'cms/slides',
                    'permission' => 'website-slider',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Daftar Unduhan',
                    'url' => 'cms/downloads',
                    'permission' => 'website-downloads',
                ],
                [
                    'icon' => 'fas fa-angle-right',
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
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Pengguna',
                    'url' => 'pengaturan/users',
                    'permission' => 'pengaturan-users',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Grup',
                    'url' => 'pengaturan/groups',
                    'permission' => 'pengaturan-group',
                ],
                [
                    'icon' => 'fas fa-angle-right',
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
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Identitas',
                    'url' => 'pengaturan/identitas',
                    'permission' => 'pengaturan-identitas',
                ],
                [
                    'icon' => 'fas fa-angle-right',
                    'text' => 'Aplikasi',
                    'url' => 'pengaturan/settings',
                    'permission' => 'pengaturan-settings',
                ],
            ],
        ],
    ];
}
