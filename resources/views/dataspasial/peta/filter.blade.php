<div class="row">
    <div class="col-md-12">
        <div id="collapse-filter" class="collapse">
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label>Sektor <span class="text-danger">*</span></label>
                        <select id="filter_sektor" class="form-control input-sm select2" data-testid="filter-sektor" required>
                            <option value="">Pilih Sektor</option>
                            @if (isset($sektor) && $sektor->isData())
                                @foreach ($sektor->data as $item)
                                    @php
                                        $attr = $item['attributes'] ?? [];
                                        $id = $item['id'] ?? '';
                                    @endphp
                                    <option value="{{ $id }}">{{ $attr['nama'] ?? ($attr['kode'] ?? $id) }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="form-group">
                        <label>Periode</label>
                        <select id="filter_periode" class="form-control input-sm select2" data-testid="filter-periode" disabled>
                            <option value="">Semua Periode</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="form-group">
                        <label>Pewarnaan Desa</label>
                        <select id="filter_tematik" class="form-control input-sm select2" data-testid="filter-tematik" disabled>
                            <option value="">Tanpa Pewarnaan</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm">
                    <div class="form-group">
                        <label>{{ config('app.sebutanKab', 'Kabupaten') }}</label>
                        <select id="filter_kabupaten" class="form-control" data-testid="filter-kabupaten"
                            title="Pilih {{ config('app.sebutanKab', 'Kabupaten') }}"></select>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="form-group">
                        <label>Kecamatan</label>
                        <select id="filter_kecamatan" class="form-control" data-testid="filter-kecamatan"
                            title="Pilih Kecamatan"></select>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="form-group">
                        <label>{{ config('app.sebutanDesa', 'Desa') }}</label>
                        <select id="filter_desa" class="form-control" data-testid="filter-desa"
                            title="Pilih {{ config('app.sebutanDesa', 'Desa') }}"></select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <button type="button" id="reset" data-testid="bt-reset" class="btn btn-secondary btn-block">
                            <span class="fas fa-ban"></span> Reset
                        </button>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <button type="button" id="filter" data-testid="bt-filter" class="btn btn-primary btn-block">
                            <span class="fas fa-search"></span> Tampilkan
                        </button>
                    </div>
                </div>
            </div>

            <hr class="mt-0">
        </div>
    </div>
</div>
