# Quickstart: Peta Gabungan Geospasial & DataSpasial

**Feature**: 2507-peta-gabungan-dashboard
**Date**: 2026-10-19

## Prasyarat
- KAB-01 selesai (menu, peran, izin `dataspasial-peta-read`).
- API-04 (`GET /api/v1/dataspasial/geojson`) tersedia; `API_DATABASE_GABUNGAN_HOST` + token `database_gabungan_api_key` terisi.
- CSP mengizinkan Leaflet + markercluster.

## Skenario manual

### US1 — Tata letak dua blok + lapisan + klaster
1. Login sebagai pengguna ber-izin `dataspasial-peta-read`; buka `/dataspasial/peta`.
2. Pastikan tampil **dua blok**: blok filter di kiri, blok lapisan/peta di kanan; ada basemap + batas wilayah.
3. Buka panel lapisan; pastikan ada lapisan kategori geospasial lama (mis. Sekolah, Fasilitas Kesehatan, Tempat Ibadah) dan lapisan per sektor DataSpasial.
4. Zoom rendah → klaster tampil dengan jumlah; klik klaster → titik individual.
5. Toggle lapisan → tampil/hilang tanpa muat ulang.
6. API tidak tersedia → pesan "Data sementara tidak tersedia", kerangka halaman utuh.

### US2 — Popup detail (sorot/klik)
1. Sorot (hover) titik → popup pratinjau; klik titik → popup menetap.
2. Popup menampilkan identitas + isian `tampil_popup` + satuan; sumber (mis. "Data Presisi") dan tanggal pembaruan bila ada.
3. Lapisan tanpa isian `tampil_popup` → popup hanya identitas.

### US3 — Filter kecamatan + isian filter (tanpa muat ulang)
1. Blok filter menampilkan dropdown **Kecamatan** (tanpa kabupaten/desa) dan kontrol untuk isian `tampil_filter` lapisan DataSpasial aktif.
2. Ubah kecamatan / isian filter → peta & legenda diperbarui tanpa muat ulang.
3. Kombinasi tanpa data → "tidak ada data untuk pilihan ini".
3. Ganti pilihan cepat → hasil pilihan terakhir yang tampil.

### US4 — Pewarnaan desa tematik
1. Pilih isian angka → desa diwarnai + legenda rentang + satuan.
2. Desa tanpa data → gaya khusus (bukan nol); desa nilai nol → kelas skala.
3. Tidak ada isian angka → pilihan pewarnaan nonaktif dengan keterangan.

### US5 — 1.000+ titik & ponsel
1. Dataset 1.000+ titik → pan/zoom lancar, klaster benar.
2. Layar ponsel: blok filter, panel lapisan, legenda, popup terjangkau; tanpa scroll horizontal.
4. Sentuh titik/klaster → popup/isi klaster terbuka dan bisa ditutup.

## Pemeriksaan otomatis
```
vendor/bin/pest tests/Feature/DataSpasial/PetaGabunganControllerTest.php
vendor/bin/pest tests/Feature/DataSpasial/DataSpasialUnavailableTest.php
vendor/bin/pest tests/Browser/SmokePetaGabunganTest.php
```

## Pemetaan SC
- SC-001 (1.000+ titik lancar), SC-002 (<3 dtk, 0 muat ulang), SC-003 (tanpa data ≠ nol), SC-004 (popup lengkap), SC-005 (ponsel), SC-006 (pesan generik), SC-007 (review SA).
