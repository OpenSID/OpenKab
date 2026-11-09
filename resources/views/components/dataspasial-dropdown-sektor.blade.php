{{--
    Dropdown SEKTOR mandiri (pakai-ulang di halaman mana pun).

    Data opsi diserahkan lewat prop `sektorList` (server-side) — komponen ini
    tidak memanggil API sendiri.

    Props:
      - sektorList : array of {kode, nama}
      - id         : id elemen select (default 'filter_sektor')
      - name       : name form (default sama dengan id)
      - label      : teks label (default 'Sektor')
      - required   : tanda wajib '*' + atribut required (default true)
      - selected   : nilai terpilih awal (opsional)
      - dataTestId : nilai data-testid (default 'filter-sektor')
      - small      : ukuran form-control-sm (default true)

    Contoh:
      <x-dataspasial-dropdown-sektor :sektor-list="$sektorList" />
      <x-dataspasial-dropdown-sektor :sektor-list="$list" id="sektor_lain" required="{{ false }}" />
--}}
@props([
    'sektorList' => [],
    'id' => 'filter_sektor',
    'name' => null,
    'label' => 'Sektor',
    'required' => true,
    'selected' => '',
    'dataTestId' => 'filter-sektor',
    'small' => true,
])

@php
    $name = $name ?: $id;
    $kelas = 'form-control'.($small ? ' form-control-sm' : '');
@endphp

<label for="{{ $id }}">{{ $label }}
    @if ($required)<span class="text-danger">*</span>@endif
</label>
<select id="{{ $id }}" name="{{ $name }}" class="{{ $kelas }}"
    data-testid="{{ $dataTestId }}" title="Pilih {{ $label }}"
    @if ($required) required @endif>
    <option value="">Pilih {{ $label }}</option>
    @foreach ($sektorList as $s)
        <option value="{{ $s['kode'] }}" @selected($selected === (string) $s['kode'])>
            {{ $s['nama'] }}
        </option>
    @endforeach
</select>
