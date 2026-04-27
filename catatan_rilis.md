Di rilis ini, versi 2604.0.0 berisi penambahan dan perbaikan yang diminta pengguna.

#### Penambahan Fitur

1. [#946](https://github.com/OpenSID/OpenKab/issues/946) Penambahan filter tahun pada statistik papan & sandang data presisi.
2. [#948](https://github.com/OpenSID/OpenKab/issues/948) Penambahan filter tahun pada statistik seni budaya & pendidikan data presisi.
3. [#952](https://github.com/OpenSID/OpenKab/issues/952) Penambahan filter tahun pada statistik Aktivitas Keagamaan, ketenagakerjaan dan adat data presisi.
4. [#942](https://github.com/OpenSID/OpenKab/issues/942) Penambahan fitur menampilkan artikel OpenSID di halaman publik.
5. [#372](https://github.com/OpenSID/API-Database-Gabungan/issues/372) Penambahan judul dan kategori ketika hapus artikel.
6. [#988](https://github.com/OpenSID/OpenKab/issues/988) Sesuaikan sort di datapresisi pangan.
7. [#995](https://github.com/OpenSID/OpenKab/issues/995) Penambahan fitur untuk laporan keaktifan desa melalui beberapa acuan.


#### Perbaikan BUG

1. [#954](https://github.com/OpenSID/OpenKab/issues/954) Perbaikan list menu tidak tampil.
2. [#369](https://github.com/OpenSID/API-Database-Gabungan/issues/369)   Perbaikan cache artikel tidak dihapus setelah operasi hapus.

#### Perubahan Teknis

1. [#943](https://github.com/OpenSID/OpenKab/issues/943) N+1 Query problem pada manajemen user.
2. [#969](https://github.com/OpenSID/OpenKab/issues/969) Terapkan CAPTCHA pada Login & Endpoint Auth untuk Batasi Bot/Bruteforce.
3. [#962](https://github.com/OpenSID/OpenKab/issues/962) Pencegahan Kerentanan XSS (Cross-Site Scripting).
4. [#966](https://github.com/OpenSID/OpenKab/issues/966) Prevent IDOR (Insecure Direct Object Reference) pada Endpoint Berbasis ID.
5. [#968](https://github.com/OpenSID/OpenKab/issues/968) Jadikan Content Security Policy (CSP) Selalu Aktif, Tidak Boleh Auto-Disable Walau di Debug/Dev.
6. [#963](https://github.com/OpenSID/OpenKab/issues/963) Enforce Strong Password Policy di Seluruh Fitur (Change/Reset/Registration).
7. [#996](https://github.com/OpenSID/OpenKab/issues/996) Perbaikan  parameter identitas OpenKab agar API hanya mengembalikan data sesuai dengan Kabupaten yang bersangkutan