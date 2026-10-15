@extends('layouts.index')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    {{--
        Editor skema kabupaten — KAB-02 (#1297).

        Tiga panel (FR-008):
          kiri   : daftar sektor + daftar skema (skema hanya muncul setelah
                   sektor dipilih — FR-007a)
          tengah : tabel isian
          kanan  : pratinjau formulir desa

        Seluruh area dibungkus SATU `<x-dataspasial-status>` agar tiga keadaan
        KAB-01 (data / kosong / tidak tersedia) tetap berlaku tanpa logika
        ganda (R10, FR-005/FR-006).
    --}}
    @include('partials.breadcrumbs')
    @include('partials.flash_message')

    <x-dataspasial-status :result="$resultSektor">
        @php
            // Keadaan editor menentukan tombol mana yang aktif (FR-008a/FR-008b).
            $bolehUbah = $statusEditor->bolehUbah();
            $bolehSimpan = $statusEditor->bolehSimpan();
            $statusNilai = $statusEditor->value;
            $sektorKode = $sektorTerpilih['kode'] ?? null;
            $skemaId = $skemaTerpilih['id'] ?? null;
        @endphp

        <div data-status-editor="{{ $statusNilai }}"
             data-sektor="{{ $sektorKode }}"
             data-skema="{{ $skemaId }}">

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

            <div class="row">
                {{-- ============ PANEL KIRI: sektor & skema ============ --}}
                <div class="col-lg-3 mb-3">
                    @include('dataspasial.skema.partials.panel-sektor', [
                        'daftarSektor' => $daftarSektor,
                        'sektorTerpilih' => $sektorTerpilih,
                        'bolehUbah' => $bolehUbah,
                    ])

                    {{--
                        Daftar skema: HANYA tampil setelah sektor dipilih (FR-007a).
                        Sengaja berada di panel kiri yang sama dengan daftar sektor.
                    --}}
                    @include('dataspasial.skema.partials.panel-skema', [
                        'skemaSektor' => $skemaSektor,
                        'skemaTerpilih' => $skemaTerpilih,
                        'sektorTerpilih' => $sektorTerpilih,
                    ])
                </div>

                {{-- ============ PANEL TENGAH: editor isian ============ --}}
                <div class="col-lg-6 mb-3">
                    @include('dataspasial.skema.partials.tabel-isian', [
                        'isian' => $isian,
                        'skemaTerpilih' => $skemaTerpilih,
                        'sektorTerpilih' => $sektorTerpilih,
                        'statusEditor' => $statusEditor,
                        'bolehUbah' => $bolehUbah,
                        'bolehSimpan' => $bolehSimpan,
                        'bolehTerbitkan' => $statusEditor->bolehSimpan(),
                        'daftarSektorSumber' => $daftarSektorSumber,
                    ])
                </div>

                {{-- ============ PANEL KANAN: pratinjau ============ --}}
                <div class="col-lg-3 mb-3">
                    @include('dataspasial.skema.partials.pratinjau', [
                        'isian' => $isian,
                        'sektorTerpilih' => $sektorTerpilih,
                    ])
                </div>
            </div>
        </div>
    </x-dataspasial-status>
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
    <script nonce="{{ csp_nonce() }}" type="module" src="{{ asset('js/dataspasial/schema-editor.js') }}"></script>
@endpush