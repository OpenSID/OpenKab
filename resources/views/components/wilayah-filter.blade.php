@props([
    'showKabupaten' => true,
    'showKecamatan' => true,
    'showDesa' => true,
    'showButtons' => true,
    'kabupatenLabel' => '',
    'kecamatanLabel' => 'Kecamatan',
    'desaLabel' => '',
    'filterButtonText' => 'TAMPILKAN',
    'clearButtonText' => 'HAPUS FILTER',
    'containerClass' => 'row',
    'columnClass' => 'col-md-3',
    'buttonColumnClass' => 'col-md-3',
    'kabupatenId' => 'filter_kabupaten',
    'kecamatanId' => 'filter_kecamatan',
    'desaId' => 'filter_desa',
    'filterButtonId' => 'bt_filter',
    'clearButtonId' => 'bt_clear_filter',
    'includeJs' => true,
])

@php
    $kabupatenLabelFinal = $kabupatenLabel ?: config('app.sebutanKab');
    $desaLabelFinal = $desaLabel ?: config('app.sebutanDesa');
@endphp

<div class="{{ $containerClass }}">
    @if ($showKabupaten)
        <div class="{{ $columnClass }}">
            <div class="form-group">
                <label>{{ $kabupatenLabelFinal }}</label>
                <select name="Filter {{ $kabupatenLabelFinal }}" id="{{ $kabupatenId }}" class="form-control"
                    title="Pilih {{ $kabupatenLabelFinal }}">
                    <option value="">Semua {{ $kabupatenLabelFinal }}</option>
                </select>
            </div>
        </div>
    @endif

    @if ($showKecamatan)
        <div class="{{ $columnClass }}">
            <div class="form-group">
                <label>{{ $kecamatanLabel }}</label>
                <select name="Filter {{ $kecamatanLabel }}" id="{{ $kecamatanId }}" class="form-control"
                    title="Pilih {{ $kecamatanLabel }}">
                    <option value="">Semua {{ $kecamatanLabel }}</option>
                </select>
            </div>
        </div>
    @endif

    @if ($showDesa)
        <div class="{{ $columnClass }}">
            <div class="form-group">
                <label>{{ $desaLabelFinal }}</label>
                <select name="Filter {{ $desaLabelFinal }}" id="{{ $desaId }}" class="form-control"
                    title="Pilih {{ $desaLabelFinal }}">
                    <option value="">Semua {{ $desaLabelFinal }}</option>
                </select>
            </div>
        </div>
    @endif

    @if ($showButtons)
        <div class="{{ $buttonColumnClass }}">
            <div class="row">
                <div class="col-6">
                    <button id="{{ $clearButtonId }}" class="btn btn-sm btn-danger btn-block">
                        {{ $clearButtonText }}
                    </button>
                </div>
                <div class="col-6">
                    <button id="{{ $filterButtonId }}" class="btn btn-sm btn-primary btn-block">
                        {{ $filterButtonText }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@if ($includeJs)
    @include('components.wilayah_filter_js')
@endif
