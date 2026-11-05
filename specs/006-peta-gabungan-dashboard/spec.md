# Feature Specification: Peta Gabungan Geospasial & DataSpasial

**Feature Branch**: `2506-peta-gabungan-dashboard`
**Created**: 2026-10-19
**Status**: Draft
**Input**: User description: "**Epic:** Peta Gabungan — data geospasial dan DataSpasial tampil menjadi satu dashboard lengkap di OpenKab. Sebagai analis OPD, saya ingin satu peta yang menampilkan titik fasilitas beserta angka DataSpasialnya, agar sebaran dan kapasitas terlihat bersamaan. Yang perlu dikerjakan: halaman peta dengan basemap, batas wilayah, dan titik dari sumber data gabungan (GET /dataspasial/geojson, API-04) dengan pengelompokan titik; popup memuat identitas fasilitas dan isian yang ditandai tampil di popup, dengan satuan; pilihan sektor dan periode, serta legenda; pewarnaan desa (tematik) menurut satu isian angka yang dipilih. Di luar lingkup: grafik dan tabel (KAB-05). Kriteria penerimaan: lancar dengan 1.000+ titik dan tampil baik di ponsel; desa tanpa data tidak diwarnai sebagai nol; mengganti sektor atau periode memperbarui peta tanpa memuat ulang halaman. Dependensi: OpenSID/OpenKab#1296 (KAB-01) klien API Satu Data, menu & peran; OpenSID/API-Database-Gabungan#469 (API-04) endpoint baca nilai, ringkasan & GeoJSON gabungan. Menjadi dasar bagi OpenSID/OpenKab#1300 (KAB-05). Terkait: OpenSID/modul_dataspasial#7 (SID-07). Tampilan acuan: OpenKab-1299_KAB-04_peta-gabungan.png — panel lapisan berisi geospasial lama dan DataSpasial dalam satu peta; filter berdasarkan isian yang bertanda filter; popup titik hanya menampilkan isian bertanda popup, dengan keterangan sumber (misalnya Data Presisi) dan tanggal pembaruan. Tata letak boleh menyesuaikan komponen OpenKab, tetapi isi dan perilakunya harus sama."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Satu Peta Gabungan: Sebaran Titik dan Batas Wilayah (Priority: P1)

Sebagai analis OPD, saya membuka menu Peta Gabungan dan melihat satu peta yang memuat basemap, batas wilayah desa, dan titik fasilitas dari data geospasial lama serta data DataSpasial secara bersamaan, sehingga sebaran fasilitas dan konteks wilayah terlihat dalam satu layar.

**Why this priority**: Ini inti epic — satu peta gabungan adalah nilai utama bagi analis; tanpa ini, seluruh fitur lain (popup, filter, pewarnaan) tidak memiliki tempat untuk tampil.

**Independent Test**: Dapat diuji dengan membuka halaman Peta Gabungan (dengan hak akses) dan memverifikasi basemap, batas wilayah, serta titik fasilitas tampil bersama, klaster titik terbentuk saat titik berdekatan, dan panel lapisan dapat menyalakan/mematikan lapisan geospasial lama serta lapisan DataSpasial secara terpisah.

**Acceptance Scenarios**:

1. **Given** pengguna berhak membuka Peta Gabungan, **When** halaman dibuka, **Then** peta tampil dengan basemap, batas wilayah, dan titik fasilitas dari sumber data gabungan dalam satu halaman.
2. **Given** terdapat banyak titik berdekatan, **When** peta dilihat pada zoom rendah, **Then** titik dikelompokkan menjadi klaster yang menampilkan jumlah titik; **When** klaster dibuka, **Then** titik-titik individual tampil.
3. **Given** panel lapisan tersedia, **When** pengguna menyalakan/mematikan lapisan geospasial lama atau lapisan DataSpasial, **Then** lapisan terkait tampil/hilang tanpa memuat ulang halaman dan tanpa menghilangkan pilihan lain.
4. **Given** data sumber tidak tersedia, **When** halaman dibuka, **Then** pesan "Data sementara tidak tersedia" tampil pada area peta dan kerangka halaman tetap utuh (bukan halaman error).

---

### User Story 2 - Popup Titik: Identitas dan Isian yang Ditandai Tampil (Priority: P1)

Sebagai analis OPD, saat mengeklik titik fasilitas, saya melihat popup berisi identitas fasilitas dan hanya isian yang ditandai tampil di popup — lengkap dengan satuan, keterangan sumber data (misalnya "Data Presisi"), dan tanggal pembaruan — sehingga saya cepat memahami kapasitas satu fasilitas tanpa membuka halaman lain.

**Why this priority**: Popup adalah cara analis menghubungkan sebaran (titik) dengan angka (kapasitas); tanpa popup yang lengkap, peta hanya sekadar peta biasa.

**Independent Test**: Dapat diuji dengan mengklik titik yang memiliki data dan memverifikasi isi popup, lalu mengklik titik dari sektor tanpa isian bertanda popup dan memverifikasi popup tetap tampil dengan identitas saja.

**Acceptance Scenarios**:

1. **Given** titik memiliki nilai pada isian yang ditandai tampil di popup, **When** titik diklik, **Then** popup menampilkan identitas fasilitas (nama/identitas dan lokasi administratifnya) beserta seluruh isian bertanda popup, masing-masing dengan nilainya dan satuannya.
2. **Given** data menyediakan keterangan sumber dan tanggal pembaruan, **When** popup dibuka, **Then** popup menampilkan sumber data (misalnya "Data Presisi") dan tanggal pembaruan.
3. **Given** sektor aktif tidak memiliki isian yang ditandai tampil di popup, **When** titik diklik, **Then** popup tetap menampilkan identitas fasilitas tanpa isian, tanpa galat.
4. **Given** titik belum memiliki nilai pada periode yang dipilih, **When** titik diklik, **Then** popup menampilkan identitas dan keterangan bahwa nilai belum tersedia untuk periode tersebut.

---

### User Story 3 - Filter Sektor, Periode, dan Isian Filter dengan Legenda (Priority: P2)

Sebagai analis OPD, saya dapat memilih sektor dan periode (serta isian yang ditandai filter) dan peta beserta legendanya langsung diperbarui tanpa memuat ulang halaman, sehingga saya dapat membandingkan sektor/periode dengan cepat.

**Why this priority**: Perbandingan cepat antar sektor dan periode yang meningkatkan nilai analisis; namun peta gabungan tanpa filter tetap berguna sebagai MVP.

**Independent Test**: Dapat diuji dengan mengganti sektor dan periode lalu memverifikasi isi peta dan legenda berubah dalam satu sesi halaman (tanpa pemuatan ulang), serta dengan memverifikasi isian yang ditandai filter tersedia sebagai kontrol filter.

**Acceptance Scenarios**:

1. **Given** halaman peta terbuka, **When** pengguna memilih sektor lain, **Then** titik, popup, dan legenda diperbarui sesuai sektor tersebut tanpa memuat ulang halaman.
2. **Given** sektor aktif memiliki lebih dari satu periode data, **When** pengguna memilih periode lain, **Then** nilai pada popup dan pewarnaan desa diperbarui sesuai periode tersebut tanpa memuat ulang halaman.
3. **Given** ada isian yang ditandai filter pada sektor aktif, **When** halaman menampilkan kontrol filter, **Then** isian bertanda filter tersedia untuk mempersempit tampilan dan peta diperbarui sesuai pilihan.
4. **Given** kombinasi filter sektor/periode tidak menghasilkan data, **When** peta diperbarui, **Then** area peta menampilkan keterangan "tidak ada data untuk pilihan ini" beserta legenda yang sesuai (kosong), bukan galat.
5. **Given** peta sedang memuat pembaruan, **When** pengguna mengganti pilihan lagi dengan cepat, **Then** yang tampil adalah hasil pilihan terakhir (bukan hasil lama yang menimpa yang baru).

---

### User Story 4 - Pewarnaan Desa Tematik Menurut Isian Angka (Priority: P2)

Sebagai analis OPD, saya dapat memilih satu isian angka dan melihat desa diwarnai menurut nilai isian tersebut beserta legenda rentang warna; desa yang tidak memiliki data tidak diwarnai sebagai nol, sehingga sebaran angka tidak menyesatkan.

**Why this priority**: Pewarnaan tematik adalah cara cepat membaca kapasitas per desa; kesalahan memperlakukan "tanpa data" sebagai nol akan menghasilkan kesimpulan analisis yang salah, sehingga aturan ini wajib sejak awal.

**Independent Test**: Dapat diuji dengan memilih isian angka pada sektor yang memiliki data dan memverifikasi desa bernilai terisi, desa bernilai nol, dan desa tanpa data tampil dengan gaya yang berbeda sesuai legenda.

**Acceptance Scenarios**:

1. **Given** sektor aktif memiliki isian angka, **When** pengguna memilih salah satu isian angka, **Then** desa diwarnai menurut nilai isian tersebut dan legenda menampilkan rentang warna beserta satuannya.
2. **Given** sebuah desa tidak memiliki data untuk isian yang dipilih, **When** pewarnaan diterapkan, **Then** desa tersebut tampil dengan gaya "tanpa data" yang berbeda jelas dari warna mana pun pada skala (termasuk berbeda dari warna nilai nol) dan tercantum pada legenda sebagai "Tanpa data".
3. **Given** sebuah desa bernilai nol pada isian yang dipilih, **When** pewarnaan diterapkan, **Then** desa diwarnai menurut skala sebagai nilai nol (nol adalah data, bukan ketiadaan data).
4. **Given** sektor aktif tidak memiliki isian angka, **When** halaman menampilkan pengaturan pewarnaan, **Then** pilihan pewarnaan tidak tersedia dengan keterangan yang jelas, dan peta tetap berfungsi menampilkan titik dan popup.

---

### User Story 5 - Lancar pada 1.000+ Titik dan Nyaman di Ponsel (Priority: P3)

Sebagai analis OPD, saya dapat menggunakan peta dengan 1.000 atau lebih titik tetap lancar (geser/zoom tidak tersendat) dan tetap dapat dipakai di ponsel, sehingga peta dapat dipakai saat rapat maupun di lapangan.

**Why this priority**: Kriteria penerimaan eksplisit dari epic; namun fitur tetap bernilai bila diuji pada skala data umum, sehingga prioritasnya di bawah fungsi inti.

**Independent Test**: Dapat diuji dengan memuat dataset berisi 1.000+ titik lalu mengukur kelancaran geser/zoom, dan dengan membuka halaman pada layar ponsel untuk menyelesaikan tugas inti (buka peta, ganti sektor, buka popup, baca legenda).

**Acceptance Scenarios**:

1. **Given** peta berisi 1.000+ titik, **When** pengguna menggeser dan memperbesar/memperkecil peta, **Then** interaksi tetap responsif (tidak tersendat berarti) dan titik tetap terkelompok dengan benar.
2. **Given** layar ponsel, **When** halaman dibuka, **Then** peta, pemilih sektor/periode, legenda, dan popup dapat dijangkau dan terbaca tanpa tata letak rusak (tidak ada elemen penting terpotong/harus digeser horizontal).
3. **Given** pengguna ponsel mengeklik/menyentuh titik atau klaster, **Then** popup atau isi klaster terbuka dan dapat ditutup kembali dengan nyaman.

---

### Edge Cases

- Sumber data tidak tersedia, timeout, atau modul data belum terpasang → tampilkan pesan "Data sementara tidak tersedia" pada area peta; kerangka halaman dan navigasi tetap utuh; jangan tampilkan halaman error/stack trace.
- Sektor tanpa isian bertanda "tampil di popup" → popup hanya berisi identitas fasilitas, tanpa galat.
- Sektor tanpa isian bertanda "filter" → kontrol filter isian tidak ditampilkan; pemilih sektor dan periode tetap ada.
- Sektor tanpa isian angka → pengaturan pewarnaan tematik tidak tersedia dengan keterangan; titik dan popup tetap berfungsi.
- Kombinasi sektor/periode/filter yang tidak menghasilkan data → area peta menampilkan keterangan kosong yang bersahabat, bukan daftar kosong tanpa penjelasan dan bukan galat.
- Desa tanpa data untuk isian terpilih → gaya "tanpa data" khusus, jangan diwarnai sebagai nol.
- Nilai nol → diperlakukan sebagai data dan diwarnai sesuai skala.
- Titik sangat berdekatan → dikelompokkan; klaster menampilkan jumlah dan dapat dibuka.
- Titik dengan koordinat tidak valid atau di luar cakupan wilayah → dilewati tanpa menggagalkan seluruh peta.
- Pengguna tanpa hak akses menu Peta Gabungan → menu tidak tampil dan akses langsung ke halaman ditolak.
- Pengguna mengganti pilihan filter saat pembaruan sedang berlangsung → hanya hasil pilihan terakhir yang ditampilkan (tidak ada hasil basi yang menimpa).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Halaman Peta Gabungan MUST menampilkan satu peta yang memuat basemap, batas wilayah administratif, dan titik fasilitas dari data geospasial lama serta data DataSpasial secara bersamaan dalam satu dashboard.
- **FR-002**: Titik fasilitas MUST dikelompokkan (klaster) saat berdekatan pada zoom rendah; klaster MUST menampilkan jumlah titik dan dapat dibuka ke titik individual.
- **FR-003**: Halaman MUST menyediakan panel lapisan untuk menyalakan/mematikan lapisan geospasial lama dan lapisan DataSpasial secara terpisah, tanpa memuat ulang halaman.
- **FR-004**: Saat titik dipilih, popup MUST menampilkan identitas fasilitas dan hanya isian yang ditandai tampil di popup; setiap nilai MUST disertai satuannya bila tersedia.
- **FR-005**: Popup MUST menampilkan keterangan sumber data (misalnya "Data Presisi") dan tanggal pembaruan bila tersedia dari sumber data.
- **FR-006**: Halaman MUST menyediakan pemilih sektor dan pemilih periode; daftar periode MUST berisi periode yang benar-benar memiliki data untuk sektor terpilih, dengan periode terbaru dipilih secara baku.
- **FR-007**: Setiap isian yang ditandai filter pada sektor aktif MUST tersedia sebagai kontrol filter yang mempersempit tampilan peta.
- **FR-008**: Mengganti sektor, periode, atau filter MUST memperbarui isi peta dan legenda tanpa memuat ulang halaman, dan MUST menampilkan hasil pilihan terakhir bila pengguna mengganti pilihan selama pemuatan berlangsung.
- **FR-009**: Halaman MUST menampilkan legenda yang selalu sesuai dengan tampilan saat ini: skala warna pewarnaan desa (termasuk rentang nilainya dan satuan) dan kategori "tanpa data"; legenda MUST ikut diperbarui saat sektor/periode/isian berubah.
- **FR-010**: Pengguna MUST dapat memilih satu isian angka dari sektor aktif sebagai dasar pewarnaan tematik desa; bila sektor aktif tidak punya isian angka, pilihan pewarnaan MUST tidak tersedia dengan keterangan yang jelas.
- **FR-011**: Desa yang tidak memiliki data untuk isian terpilih MUST TIDAK diwarnai sebagai nol; desa tersebut MUST tampil dengan gaya "tanpa data" yang berbeda jelas dari seluruh warna skala dan tercantum pada legenda sebagai "Tanpa Data".
- **FR-012**: Desa yang bernilai nol pada isian terpilih MUST diwarnai menurut skala sebagai nilai nol (nol = ada data).
- **FR-013**: Peta MUST tetap responsif (geser/zoom lancar) dengan setidaknya 1.000 titik, termasuk saat pewarnaan desa dan pengelompokan titik aktif bersamaan.
- **FR-014**: Tata letak halaman MUST tetap terpakai pada layar ponsel: pemilih sektor/periode, legenda, panel lapisan, dan popup MUST dapat dijangkau dan terbaca tanpa elemen penting terpotong.
- **FR-015**: Akses halaman MUST mengikuti kontrol akses menu DataSpasial yang sudah ada (KAB-01): pengguna tanpa hak MUST tidak melihat menu dan MUST ditolak bila mengakses URL secara langsung.
- **FR-016**: Ketika sumber data tidak tersedia (layanan mati, timeout, konfigurasi belum lengkap, atau modul data belum terpasang), halaman MUST menampilkan pesan "Data sementara tidak tersedia" pada area terkait dan MUST NOT menampilkan halaman error atau stack trace.
- **FR-017**: Grafik, tabel, dan ekspor indikator MUST TIDAK disertakan dalam fitur ini (lingkup KAB-05); namun struktur halaman MUST memungkinkan fitur tersebut ditambahkan kemudian tanpa mengubah perilaku peta yang sudah ada.

### Key Entities *(include if feature involves data)*

- **Fasilitas (Titik)**: Objek geografis berupa titik lokasi fasilitas; memiliki identitas (nama/jenis), lokasi administratif (desa/kecamatan/kabupaten), koordinat, dan kumpulan nilai per isian pada suatu periode.
- **Isian**: Satu kolom data pada suatu sektor; memiliki label, tipe (angka atau teks), satuan, dan penanda perilaku: ditandai *tampil di popup*, ditandai *filter*, dan/atau layak dijadikan dasar pewarnaan (tipe angka).
- **Sektor**: Kelompok besar data (misalnya pendidikan, kesehatan) yang menjadi pemilih utama di halaman; setiap sektor punya kumpulan isian, titik, dan periode.
- **Periode**: Waktu rilis data (tahun-bulan) tempat nilai-nilai berlaku; periode hanya ditawarkan bila benar-benar memiliki data untuk sektor terpilih.
- **Nilai**: Isian sebuah fasilitas/desa pada periode tertentu; dapat berupa angka atau teks, disertai satuan, sumber data, dan tanggal pembaruan.
- **Wilayah (Desa)**: Batas administratif yang menjadi dasar pewarnaan tematik; statusnya terhadap isian terpilih: bernilai (termasuk nol) atau tanpa data.
- **Legenda**: Tampilan pemetaan visual saat ini: rentang warna + satuan untuk pewarnaan desa dan kategori "Tanpa Data"; diperbarui mengikuti pilihan pengguna.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Dengan dataset berisi 1.000 titik atau lebih, pengguna dapat menggeser dan zoom peta tanpa tersendat berarti — 95% interaksi geser/zoom merespons dalam waktu terasa di bawah ±1 detik pada desktop maupun ponsel uji.
- **SC-002**: Mengganti sektor atau periode memperbarui isi peta dan legenda dalam 3 detik atau lebih cepat, dengan 0 kali memuat ulang halaman pada seluruh skenario pengujian filter.
- **SC-003**: Pada dataset uji yang memuat desa tanpa data, desa bernilai nol, dan desa bernilai positif, 100% desa tanpa data tampil dengan gaya "tanpa data" yang berbeda dari warna nilai nol; 0 desa tanpa data tampil terwarnai sebagai nol.
- **SC-004**: Pada titik yang memiliki data, 100% popup menampilkan identitas fasilitas dan seluruh isian bertanda popup lengkap dengan satuan; keterangan sumber dan tanggal pembaruan tampil pada 100% titik yang datanya menyediakan keduanya.
- **SC-005**: Pada pengujian ponsel, tugas inti (buka peta → ganti sektor → buka popup → baca legenda) dapat diselesaikan dalam waktu di bawah 1 menit tanpa kegagalan akibat tata letak, oleh seluruh pengguna uji.
- **SC-006**: Saat sumber data tidak tersedia, 100% kunjungan menampilkan pesan "Data sementara tidak tersedia" dengan kerangka halaman utuh; 0 kunjungan menampilkan halaman error.
- **SC-007**: Seluruh kriteria penerimaan pada user story 1–4 dipenuhi dan direview System Analyst sebelum dinyatakan selesai.

## Assumptions

- Dependensi KAB-01 (OpenSID/OpenKab#1296 — klien API Satu Data, menu, peran/hak akses) dan API-04 (OpenSID/API-Database-Gabungan#469 — endpoint baca nilai, ringkasan, dan GeoJSON gabungan) telah selesai; fitur ini hanya mengonsumsinya dan tidak mendefinisikan ulang kontraknya.
- Sumber data gabungan (API-04) menyediakan geometri titik, nilai per isian/periode, penanda isian (*popup*, *filter*), satuan, sumber data, dan tanggal pembaruan dalam satu pemanggilan baca geospasial.
- Lapisan "geospasial lama" adalah kemampuan peta/wilayah yang sudah ada di OpenKab saat ini; fitur ini menampilkannya kembali sebagai lapisan yang dapat dihidupkan/matikan, bukan membangun data geospasial baru.
- Pewarnaan desa memakai nilai isian tingkat desa sebagaimana disediakan sumber data (termasuk ringkasan/agregat bila isian bersifat per fasilitas); OpenKab tidak menghitung ulang agregasi sendiri.
- Pengaturan akses memakai model peran/hak akses DataSpasial yang sudah ada (termasuk peran Analis OPD); tidak ada peran baru yang dibuat untuk fitur ini.
- Bila pengguna tidak memilih periode, sistem memakai periode terbaru yang memiliki data.
- Klasifikasi skala warna memakai pembagian rentang yang wajar untuk data tersedia (misalnya kuantil atau rentang sama besar); detail rumus klasifikasi bukan kebutuhan bisnis dan dapat ditentukan saat perencanaan implementasi.
- Dukungan peramban: versi modern desktop dan ponsel yang umum dipakai; tanpa dukungan peramban lama.
- Tangkapan layar acuan (OpenKab-1299_KAB-04_peta-gabungan.png) mengikat isi dan perilaku, bukan tata letak piksel; tata letak menyesuaikan komponen OpenKab yang ada.
- Pengujian kinerja dan ponsel memakai dataset uji sintetis berisi 1.000+ titik dan sebaran desa dengan nilai, nol, dan tanpa data.
