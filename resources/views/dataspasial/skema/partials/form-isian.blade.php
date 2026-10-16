{{--
    Satu baris tabel isian = satu kolom formulir desa (FR-009).

    ⚠️ Nama field di sini adalah NAMA INTERNAL (`popupPeta`, `sebagaiFilter`,
    `sebagaiIndikator`, ). Nama field ASUMSI kontrak API
    (`popup_peta`, `filter`, `indikator`, `pemetaan`) **dilarang** muncul di
    view — pemetaannya hanya di `SkemaEditorService` (research.md R12).

    ⚠️ Kolom satuan hanya relevan bila tipe `angka` (FR-010,
    `TipeIsian::memakaiSatuan()`).
--}}
@php
    $tipe = \App\Enums\TipeIsian::hasValue($baris['tipe'])
        ? \App\Enums\TipeIsian::fromValue($baris['tipe'])
        : \App\Enums\TipeIsian::Teks();
    $memakaiSatuan = $tipe->memakaiSatuan();
    // ⚠️ VERIFIKASI LANGSUNG: `sumber` berisi ENUM API (`openSID`/`dataPresisi`/
    // `impor`), BUKAN kode sektor dan bukan literal 'pemetaan' (kontrak §6a.5).
    $sumber = $baris['sumber'] ?? 'manual';
    $terpetakan = $sumber !== null && $sumber !== '' && $sumber !== 'manual';
@endphp

<tr data-testid="baris-isian"
    data-index="{{ $index }}"
    data-opsi="{{ Illuminate\Support\Js::from($baris['opsi'] ?? []) }}"
    draggable="{{ $bolehUbah ? 'true' : 'false' }}">

    {{-- Handle seret (FR-012). --}}
    <td class="text-center handle-seret align-middle" title="Seret untuk mengurutkan">
        @if ($bolehUbah)
            <i class="fas fa-grip-vertical text-muted"></i>
        @endif
    </td>

    <td class="text-center align-middle urutan text-muted">{{ $baris['urutan'] }}</td>

    <td>
        <input type="text" class="form-control form-control-sm input-label" maxlength="255"
               value="{{ $baris['label'] }}" placeholder="Jumlah Siswa"
               @disabled(!$bolehUbah)>
        @error("isian.{$index}.label")
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </td>

    <td>
        <select class="form-control form-control-sm input-tipe" @disabled(!$bolehUbah)>
            @foreach (\App\Enums\TipeIsian::getValues() as $nilai)
                <option value="{{ $nilai }}" @selected($nilai === $baris['tipe'])>
                    {{ $nilai }}
                </option>
            @endforeach
        </select>
        @error("isian.{$index}.tipe")
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </td>

    <td class="text-center">
        <input type="checkbox" class="input-wajib" @checked($baris['wajib']) @disabled(!$bolehUbah)>
    </td>

    <td>
        <input type="text" class="form-control form-control-sm input-satuan" maxlength="50"
               value="{{ $baris['satuan'] ?? '' }}" placeholder="{{ $memakaiSatuan ? 'orang' : '—' }}"
               @disabled(!$bolehUbah || ! $memakaiSatuan)>
    </td>

    {{--
        ⚠️ Kolom **Pilihan (opsi) tidak lagi ditampilkan** — tidak dipakai pada
        editor ini. Nilai `opsi` yang tersimpan di API tetap **dipertahankan**
        melalui atribut `data-opsi` pada baris sehingga `bacaDariDom()`
        mengirimkannya kembali saat Simpan draf dan tidak terjadi kehilangan data.
    --}}

    {{--
        Sumber nilai: manual atau pemetaan (FR-023).
        Memakai `data-*` sehingga JS bisa menulis/membaca status pemetaan
        tanpa perlu mem-parsing teks badge.
    --}}
    <td class="sumber-nilai align-middle"
        data-sumber="{{ $sumber }}"
        data-tabel-sumber="{{ $baris['tabelSumber'] ?? '' }}"
        data-kolom-sumber="{{ $baris['kolomSumber'] ?? '' }}"
        data-rujukan="{{ $baris['rujukan'] ?? '' }}">
        @if ($bolehUbah)
            <button type="button" class="btn btn-outline-secondary btn-sm btn-pilih-sumber-baris px-1 py-0"
                    title="Pilih sumber nilai">
                @if ($terpetakan)
                    <span class="badge badge-info" data-testid="badge-terpetakan">
                        <i class="fas fa-lock mr-1"></i>{{ $sumber }}
                    </span>
                    <input type="text" class="input-tabel-sumber form-control form-control-sm mt-1"
                           value="{{ $baris['tabelSumber'] ?? '' }}" placeholder="tabel sumber"
                           title="Tabel sumber data">
                    <input type="text" class="input-kolom-sumber form-control form-control-sm mt-1"
                           value="{{ $baris['kolomSumber'] ?? '' }}" placeholder="kolom sumber"
                           title="Kolom sumber data">
                @else
                    <span class="badge badge-secondary" data-testid="badge-manual">Manual</span>
                @endif
            </button>
        @else
            @if ($terpetakan)
                <span class="badge badge-info">{{ $sumber }}</span>
                <span class="text-muted small d-block">{{ $baris['tabelSumber'] ?? '' }}.{{ $baris['kolomSumber'] ?? '' }}</span>
            @else
                <span class="badge badge-secondary" data-testid="badge-manual">Manual</span>
            @endif
        @endif
    </td>

    <td class="text-center">
        <input type="checkbox" class="input-popup-peta" @checked($baris['popupPeta']) @disabled(!$bolehUbah)>
    </td>

    <td class="text-center">
        <input type="checkbox" class="input-filter" @checked($baris['sebagaiFilter']) @disabled(!$bolehUbah)>
    </td>

    <td class="text-center">
        <input type="checkbox" class="input-indikator" @checked($baris['sebagaiIndikator']) @disabled(!$bolehUbah)>
    </td>

    <td class="text-center">
        @if ($bolehUbah)
            <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-isian"
                    title="Hapus isian" data-testid="btn-hapus-isian"
                    data-kode="{{ $baris['kode'] }}" data-terpetakan="{{ $terpetakan ? '1' : '0' }}">
                <i class="fas fa-trash-alt"></i>
            </button>
        @endif
    </td>
</tr>