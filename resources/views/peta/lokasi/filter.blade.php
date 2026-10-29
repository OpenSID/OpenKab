<div class="row">
    <div class="col-md-12">
        <div id="collapse-filter" class="collapse">
            <div class="row">
                <div class="col-sm">
                    <div class="form-group">
                        <label>Status</label>
                        <select id="status" class="form-control input-sm select2" data-testid="filter-status">
                            <option value="">Semua Status</option>
                            @foreach ($status as $key => $item)
                                <option value="{{ $key }}">{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="form-group">
                        <label>Jenis Lokasi</label>
                        <select id="point" class="form-control input-sm select2" data-testid="filter-point">
                            <option value="">Semua Jenis</option>
                            @foreach ($point as $item)
                                <option data-children='{!! json_encode($item['children']) !!}' value="{{ $item['id'] }}">{{ $item['nama'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="form-group">
                        <label>Kategori Lokasi</label>
                        <select id="subpoint" class="form-control input-sm select2" data-testid="filter-subpoint">
                            <option value="">Semua Kategori</option>
                            @foreach ($point as $item)
                                <optgroup label="{{ $item['nama'] }}">
                                    @foreach ($item['children'] as $child)
                                        <option value="{{ $child['id'] }}">{{ $child['nama'] }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
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
