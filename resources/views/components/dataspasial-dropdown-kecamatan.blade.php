{{--
    Dropdown KECAMATAN mandiri (pakai-ulang di halaman mana pun).

    Opsi diambil langsung dari `ConfigApiService::kecamatan()` di server
    (bisa difilter lewat prop `filters`, mis. `['filter[kode_kabupaten]'=>...]`).
    Opsi pertama selalu "Semua Kecamatan" (value '').

    Props:
      - id         : id elemen select (default 'filter_kecamatan')
      - name       : name form (default sama dengan id)
      - label      : teks label (default 'Kecamatan')
      - semuaLabel : label opsi semua (default 'Semua Kecamatan')
      - selected   : kode kecamatan terpilih awal (opsional)
      - dataTestId : nilai data-testid (default 'filter-kecamatan')
      - small      : ukuran form-control-sm (default true)
      - options    : pra-isian server-side opsional [{kode_kecamatan, nama_kecamatan}]
                     (bila null, diambil otomatis dari ConfigApiService)
      - filters    : filter untuk ConfigApiService::kecamatan() (opsional)

    Contoh:
      <x-dataspasial-dropdown-kecamatan />
      <x-dataspasial-dropdown-kecamatan :filters="['filter[kode_kabupaten]' => '...']" />
--}}
@props([
    'id' => 'filter_kecamatan',
    'name' => null,
    'label' => 'Kecamatan',
    'semuaLabel' => 'Semua Kecamatan',
    'selected' => '',
    'dataTestId' => 'filter-kecamatan',
    'small' => true,
    'options' => null,
    'filters' => [],
])

@php
    $name = $name ?: $id;
    $kelas = 'form-control'.($small ? ' form-control-sm' : '');

    if ($options === null) {
        $kecamatans = app(\App\Services\ConfigApiService::class)->kecamatan($filters);

        $options = $kecamatans->map(fn ($item) => [
            'kode_kecamatan' => is_object($item) ? ($item->kode_kecamatan ?? null) : ($item['kode_kecamatan'] ?? null),
            'nama_kecamatan' => is_object($item) ? ($item->nama_kecamatan ?? ($item->kode_kecamatan ?? '')) : ($item['nama_kecamatan'] ?? ($item['kode_kecamatan'] ?? '')),
        ])->filter(fn ($o) => ! empty($o['kode_kecamatan']))->values()->all();
    }
@endphp

<label for="{{ $id }}">{{ $label }}</label>
<select id="{{ $id }}" name="{{ $name }}" class="{{ $kelas }}"
    data-testid="{{ $dataTestId }}" title="Pilih {{ $label }}">
    <option value="">{{ $semuaLabel }}</option>
    @foreach ($options as $o)
        <option value="{{ $o['kode_kecamatan'] }}" @selected((string) $selected === (string) $o['kode_kecamatan'])>
            {{ $o['nama_kecamatan'] }}
        </option>
    @endforeach
</select>
