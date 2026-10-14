@props([
    'result',
])

{{--
    Merender tiga keadaan hasil pemanggilan API Satu Data (FR-005).

    - `unavailable` → pesan "data sementara tidak tersedia" (FR-014)
    - `empty`       → keadaan kosong yang jelas (FR-015)
    - `data`        → $slot (kerangka konten menu)

    Halaman selalu merespons HTTP 200 sehingga kerangka halaman, sidebar,
    dan navigasi tetap utuh.
--}}
@if ($result->isUnavailable())
    @include('dataspasial.unavailable')
@elseif ($result->isEmpty())
    @include('dataspasial.empty')
@else
    {{ $slot }}
@endif
