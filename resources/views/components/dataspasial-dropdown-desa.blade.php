{{--
    Dropdown DESA mandiri (pakai-ulang di halaman mana pun).

    Data opsi diserahkan lewat prop `desaList` (server-side) — komponen ini
    tidak memanggil API sendiri. Cocok dipakai bersamaan dengan
    `<x-dataspasial-dropdown-kecamatan>` karena tiap opsi dapat menyimpan
    `data-kode-kecamatan` untuk pemfilteran sisi klien.

    Props:
      - desaList         : array of {kode_desa, nama_desa, kode_kecamatan}
      - id               : id elemen select (default 'filter_desa')
      - name             : name form (default sama dengan id)
      - label            : teks label (default 'Desa')
      - semuaLabel       : label opsi semua (default 'Semua Desa')
      - selected         : kode desa terpilih awal (opsional)
      - dataTestId       : nilai data-testid (default 'filter-desa')
      - small            : ukuran form-control-sm (default true)
      - filterByKecamatan: aktifkan atribut data-kode-kecamatan (default true)
      - required         : atribut required (default false)

    Contoh:
      <x-dataspasial-dropdown-desa :desa-list="$desaList" />
      <x-dataspasial-dropdown-desa :desa-list="$desaList" id="desa_audit" data-testid="filter-desa" />
--}}
@props([
    'desaList' => [],
    'id' => 'filter_desa',
    'name' => null,
    'label' => 'Desa',
    'semuaLabel' => 'Semua Desa',
    'selected' => '',
    'dataTestId' => 'filter-desa',
    'small' => true,
    'filterByKecamatan' => true,
    'required' => false,
])

@php
    $name = $name ?: $id;
    $kelas = 'form-control'.($small ? ' form-control-sm' : '');
@endphp

<label for="{{ $id }}">{{ $label }}</label>
<select id="{{ $id }}" name="{{ $name }}" class="{{ $kelas }}"
    data-testid="{{ $dataTestId }}" title="Pilih {{ $label }}"
    @if ($required) required @endif>
    <option value="">{{ $semuaLabel }}</option>
    @foreach ($desaList as $d)
        @php
            $kodeDesa = is_array($d) ? ($d['kode_desa'] ?? ($d['kode'] ?? '')) : (is_object($d) ? ($d->kode_desa ?? ($d->kode ?? '')) : '');
            $namaDesa = is_array($d) ? ($d['nama_desa'] ?? ($d['nama'] ?? $kodeDesa)) : (is_object($d) ? ($d->nama_desa ?? ($d->nama ?? $kodeDesa)) : $kodeDesa);
            $kodeKecamatan = is_array($d) ? ($d['kode_kecamatan'] ?? '') : (is_object($d) ? ($d->kode_kecamatan ?? '') : '');
        @endphp
        @if ($kodeDesa !== '')
            <option value="{{ $kodeDesa }}" @if ($filterByKecamatan && $kodeKecamatan !== '') data-kode-kecamatan="{{ $kodeKecamatan }}" @endif
                @selected((string) $selected === (string) $kodeDesa)>
                {{ $namaDesa }}
            </option>
        @endif
    @endforeach
</select>
