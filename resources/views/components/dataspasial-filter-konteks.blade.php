{{--
    Wrapper filter pakai-ulang KAB-07 (FR-005 / DoD #1303).

    Merangkai dua komponen dropdown MANDIRI:
      - <x-dataspasial-dropdown-sektor>
      - <x-dataspasial-dropdown-kecamatan>
    plus periode & tombol Tampilkan/Reset. Halaman lain dapat memakai
    komponen dropdown satu per satu tanpa wrapper ini.

    Props:
      - sektorList          : array {kode, nama} untuk dropdown sektor
      - semuaKecamatanLabel : label opsi "semua kecamatan" (default "Semua Kecamatan")
      - showButtons         : tampilkan tombol Tampilkan/Reset (default true)
      - dataUrl / eksporUrl / aturan*Url : URL AJAX (diinisialisasi monitoring.js)
      - aturanWrite         : tampilkan panel kelola aturan (boolean)
--}}
@props([
    'sektorList' => [],
    'semuaKecamatanLabel' => 'Semua Kecamatan',
    'showButtons' => true,
    'dataUrl' => '',
    'eksporUrl' => '',
    'aturanUrl' => '',
    'aturanSimpanUrl' => '',
    'aturanToggleUrl' => '',
    'aturanWrite' => false,
])

<div class="peta-filter" id="monitoring-filter" data-testid="monitoring-filter">
    <div class="row">
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <x-dataspasial-dropdown-sektor :sektor-list="$sektorList" />
        </div>
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <x-dataspasial-dropdown-kecamatan :semua-label="$semuaKecamatanLabel" />
        </div>
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <label for="filter_periode">Periode</label>
            <select id="filter_periode" class="form-control form-control-sm" data-testid="filter-periode"
                title="Pilih Periode">
                <option value="">Terbaru</option>
            </select>
        </div>
    </div>

    @if ($showButtons)
        <div class="row">
            <div class="col-md-6 col-12 mb-2">
                <button type="button" id="bt-reset" class="btn btn-secondary btn-block" data-testid="bt-reset">
                    <span class="fas fa-ban"></span> Reset
                </button>
            </div>
            <div class="col-md-6 col-12 mb-2">
                <button type="button" id="bt-filter" class="btn btn-primary btn-block" data-testid="bt-filter">
                    <span class="fas fa-search"></span> Tampilkan
                </button>
            </div>
        </div>
    @endif

    <div id="monitoring-no-context" class="alert alert-info mb-0" data-testid="monitoring-no-context">
        Pilih <strong>sektor</strong> terlebih dahulu untuk menampilkan data monitoring.
        Kecamatan dan periode bersifat opsional (kosong = semua).
    </div>
</div>

@php
    $jsOptions = [
        'dataUrl' => $dataUrl,
        'eksporUrl' => $eksporUrl,
        'aturanUrl' => $aturanUrl,
        'aturanSimpanUrl' => $aturanSimpanUrl,
        'aturanToggleUrl' => $aturanToggleUrl,
        'aturanWrite' => (bool) $aturanWrite,
        'semuaKecamatan' => $semuaKecamatanLabel,
    ];
@endphp

<script nonce="{{ csp_nonce() }}">
    window.MonitoringFilterKomponen = @json($jsOptions);
</script>
