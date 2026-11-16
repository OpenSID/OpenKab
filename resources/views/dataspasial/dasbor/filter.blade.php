{{--
    Blok filter dasbor — posisi paling atas, selebar halaman.
--}}
<div class="peta-filter" id="dasbor-filter" data-testid="dasbor-filter">
    <div class="row">
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <label for="filter_kecamatan">Kecamatan</label>
            <select id="filter_kecamatan" class="form-control form-control-sm" data-testid="filter-kecamatan"
                title="Pilih Kecamatan">
                <option value="">Semua Kecamatan</option>
            </select>
        </div>
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <label for="filter_desa">Desa</label>
            <select id="filter_desa" class="form-control form-control-sm" data-testid="filter-desa"
                title="Pilih Desa">
                <option value="">Semua Desa</option>
            </select>
        </div>
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <label for="filter_sektor">Sektor</label>
            <select id="filter_sektor" class="form-control form-control-sm" data-testid="filter-sektor"
                title="Pilih Sektor">
                <option value="">Semua Sektor</option>
                @if(isset($sektorList))
                    @foreach($sektorList as $s)
                        <option value="{{ $s['kode'] }}">{{ $s['nama'] }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <label for="filter_periode">Periode</label>
            <select id="filter_periode" class="form-control form-control-sm" data-testid="filter-periode"
                title="Pilih Periode">
                <option value="">Terbaru</option>
            </select>
        </div>
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <label for="filter_indikator">Indikator</label>
            <select id="filter_indikator" class="form-control form-control-sm" data-testid="filter-indikator"
                title="Pilih Indikator">
                <option value="">Pilih Indikator</option>
                @if(isset($indikatorBySektor))
                    @foreach($indikatorBySektor as $sk => $items)
                        @foreach($items as $it)
                            <option value="{{ $it['kode'] }}" data-sektor="{{ $sk }}">{{ $it['label'] }}</option>
                        @endforeach
                    @endforeach
                @endif
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-2">
            <div class="d-flex flex-wrap">
                <button type="button" id="bt-salin-tautan" class="btn btn-outline-primary btn-sm mr-1 mb-1"
                    data-testid="bt-salin-tautan"><span class="fas fa-link"></span> Salin tautan filter</button>
                <button type="button" id="bt-ekspor" class="btn btn-primary btn-sm mr-1 mb-1"
                    data-testid="bt-ekspor"><span class="fas fa-file-excel"></span> Ekspor Excel</button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-12 mb-2">
            <button type="button" id="peta-reset" class="btn btn-secondary btn-block"
                data-testid="bt-reset"><span class="fas fa-ban"></span> Reset</button>
        </div>
        <div class="col-md-6 col-12 mb-2">
            <button type="button" id="peta-filter" class="btn btn-primary btn-block"
                data-testid="bt-filter"><span class="fas fa-search"></span> Tampilkan</button>
        </div>
    </div>

    <div id="peta-filters" data-testid="peta-filters" style="display:none"></div>
</div>
