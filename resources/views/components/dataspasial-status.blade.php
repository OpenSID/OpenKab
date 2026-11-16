@props([
    'result',
    'tetapTampilkanKonten' => false,
])

{{--
    Merender tiga keadaan hasil pemanggilan API Satu Data (FR-005).

    - `unavailable` → pesan "data sementara tidak tersedia" (FR-014)
    - `empty`       → keadaan kosong yang jelas (FR-015)
    - `data`        → $slot (kerangka konten menu)

    `tetapTampilkanKonten` (Q-empty-sektor): bila true, keadaan `empty` ikut
    merender $slot sehingga editor sektor dan tombol tambah tetap tampil;
    keadaan kosong dijelaskan oleh konten slot itu sendiri.

    Halaman selalu merespons HTTP 200 sehingga kerangka halaman, sidebar,
    dan navigasi tetap utuh.
--}}
@if ($result->isUnavailable())
    @include('dataspasial.unavailable')
@elseif ($result->isEmpty() && ! $tetapTampilkanKonten)
    @include('dataspasial.empty')
@else
    {{ $slot }}
@endif
