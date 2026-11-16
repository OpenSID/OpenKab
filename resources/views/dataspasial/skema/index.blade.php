@extends('layouts.index')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    {{--
        Editor skema kabupaten — KAB-02 (#1297).

        Dua panel (FR-008, Q22):
          kiri   : daftar sektor
          kanan  : tabel isian + pratinjau formulir desa yang dapat dibuka

        Tidak ada lagi daftar skema maupun nomor versi di UI — daftar isian
        selalu berasal dari skema terakhir sektor yang dipilih.

        Seluruh area dibungkus SATU `<x-dataspasial-status>` agar tiga keadaan
        KAB-01 (data / kosong / tidak tersedia) tetap berlaku tanpa logika
        ganda (R10, FR-005/FR-006).
    --}}
    @include('partials.breadcrumbs')
    @include('partials.flash_message')

    {{-- Q-empty-sektor: keadaan kosong tetap merender editor (panel sektor + tombol tambah). --}}
    <x-dataspasial-status :result="$resultSektor" :tetap-tampilkan-konten="true">
        @php
            // Keadaan editor menentukan tombol mana yang aktif (FR-008a/FR-008b).
            $bolehUbah = $statusEditor->bolehUbah();
            $bolehSimpan = $statusEditor->bolehSimpan();
            $statusNilai = $statusEditor->value;
            $sektorKode = $sektorTerpilih['kode'] ?? null;
            $sektorNama = $sektorTerpilih['nama'] ?? '';
        @endphp

        <div data-status-editor="{{ $statusNilai }}"
             data-sektor="{{ $sektorKode }}"
             data-sektor-nama="{{ $sektorNama }}"
             data-mode-sektor-baru="{{ $modeSektorBaru ? '1' : '0' }}">

            @if ($modeSektorBaru)
                <div class="alert alert-info border-0 shadow-sm" role="alert" data-testid="keadaan-sektor-kosong">
                    <h6 class="alert-heading mb-1">Belum ada sektor</h6>
                    <p class="mb-0">
                        DataSpasial belum memiliki sektor. Isi nama sektor dan minimal satu isian,
                        lalu tekan <strong>Simpan draf</strong> untuk membuat sektor pertama.
                    </p>
                </div>
            @endif

            {{-- Error validasi per baris isian (FR-017). --}}
            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm" role="alert" data-testid="validasi-draf">
                    <h6 class="alert-heading">Draf belum dapat disimpan</h6>
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($versiTidakDitemukan)
                <div class="alert alert-warning border-0 shadow-sm" role="alert" data-testid="versi-tidak-ditemukan">
                    <h6 class="alert-heading mb-1">Versi tidak ditemukan atau sudah tidak berlaku</h6>
                    <p class="mb-2">Versi skema pada tautan tidak tersedia. Pilih kembali sektor dari daftar.</p>
                    <a href="{{ route('dataspasial.skema') }}" class="btn btn-sm btn-outline-secondary"
                       data-testid="btn-kembali-daftar-sektor">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke daftar sektor
                    </a>
                </div>
            @endif

            <div class="row">
                {{-- ============ PANEL KIRI: sektor ============ --}}
                <div class="col-lg-3 mb-3">
                    @include('dataspasial.skema.partials.panel-sektor', [
                        'daftarSektor' => $daftarSektor,
                        'sektorTerpilih' => $sektorTerpilih,
                        'bolehUbah' => $bolehUbah,
                    ])
                </div>

                {{-- ============ EDITOR ISIAN DAN PRATINJAU ============ --}}
                <div class="col-lg-9 mb-3 {{ $versiTidakDitemukan ? 'd-none' : '' }}">
                    @include('dataspasial.skema.partials.tabel-isian', [
                        'isian' => $isian,
                        'sektorTerpilih' => $sektorTerpilih,
                        'statusEditor' => $statusEditor,
                        'bolehUbah' => $bolehUbah,
                        'bolehSimpan' => $bolehSimpan,
                        'bolehTerbitkan' => $statusEditor->bolehSimpan(),
                    ])
                    <details class="mt-3">
                        <summary class="btn btn-outline-secondary" data-testid="toggle-pratinjau">
                            <i class="fas fa-eye mr-1"></i>Pratinjau formulir desa
                        </summary>
                        <div class="mt-2">
                            @include('dataspasial.skema.partials.pratinjau', [
                                'isian' => $isian,
                                'sektorTerpilih' => $sektorTerpilih,
                            ])
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </x-dataspasial-status>

    {{--
        KAB-03 — layar terbitan: dialog di atas halaman skema, dipicu tombol
        `#btn-terbitan`. Tidak ada route/menu baru (clarify 2026-10-16 Q1 → A).
    --}}
    @include('dataspasial.skema.partials.layar-terbitan')
@stop

@push('js')
    {{--
        Skrip editor dimuat sebagai aset statis (pola `asset()` yang sama dengan
        `resources/views/group/menu.blade.php`), BUKAN via bundler Vite.

        Alasannya: `npm run build` saat ini gagal karena `app.scss` mengimpor
        `daterangepicker` yang tidak terpasang dan tidak terdaftar di
        `package.json` — masalah lama yang TIDAK terkait fitur ini dan tidak
        diselesaikan di sini (Prinsip V: jangan menambah dependensi).
    --}}
    {{--
        FR-008g/Q27 — SweetAlert2 untuk seluruh pesan pada halaman editor skema.

        ⚠️ Dimuat via CDN (pola yang sama dengan
        `resources/views/penduduk/index.blade.php:95`) karena bundle Vite yang
        sedang ter-build di `public/build/assets/app-*.js` **belum memuat**
        `sweetalert2`, sehingga `window.Swal` belum tersedia dari bundle.
    --}}
    <script nonce="{{ csp_nonce() }}" src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script nonce="{{ csp_nonce() }}" type="module" src="{{ asset('js/dataspasial/schema-editor.js') }}"></script>
    {{-- KAB-03 — layar terbitan (ringkasan, riwayat, adopsi, ekspor). --}}
    <script nonce="{{ csp_nonce() }}" type="module" src="{{ asset('js/dataspasial/terbit-dialog.js') }}"></script>
@endpush