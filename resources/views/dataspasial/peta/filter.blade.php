{{--
    Blok filter DataSpasial (kiri).
    Berisi: filter kecamatan (hanya kecamatan) + kontrol dinamis untuk setiap
    isian bertanda `tampil_filter` pada lapisan DataSpasial aktif.
    Tidak ada pemilih sektor.
--}}
<div class="peta-filter">
    <div class="form-group">
        <label for="filter_kecamatan">Kecamatan</label>
        <select id="filter_kecamatan" class="form-control form-control-sm" data-testid="filter-kecamatan"
            title="Pilih Kecamatan">
            <option value="">Semua Kecamatan</option>
        </select>
    </div>

    <div id="peta-filters" data-testid="peta-filters">
        <p class="text-muted small mb-0" id="peta-filters-hint">
            Pilih lapisan DataSpasial untuk menampilkan filter isian.
        </p>
    </div>

    <hr class="my-2">

    <div class="form-group mb-2">
        <label for="filter_tematik">Pewarnaan Desa</label>
        <select id="filter_tematik" class="form-control form-control-sm" data-testid="filter-tematik" disabled>
            <option value="">Tanpa Pewarnaan</option>
        </select>
    </div>

    <div class="row">
        <div class="col-6">
            <button type="button" id="peta-reset" data-testid="bt-reset" class="btn btn-secondary btn-sm btn-block">
                <span class="fas fa-ban"></span> Reset
            </button>
        </div>
        <div class="col-6">
            <button type="button" id="peta-filter" data-testid="bt-filter" class="btn btn-primary btn-sm btn-block">
                <span class="fas fa-search"></span> Tampilkan
            </button>
        </div>
    </div>
</div>
