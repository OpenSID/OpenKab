<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form-label"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-lokasi" method="POST" action="{{ route('plan.store') }}">
            @csrf
            <input type="hidden" name="_method" id="lokasi-method" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-form-label">Tambah Data Lokasi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="lokasi-id" value="">

                    <div class="form-group">
                        <label for="lokasi-kode-desa">Nama Desa</label>
                        <select id="lokasi-kode-desa" name="kode_desa" class="form-control" required
                            data-testid="lokasi-kode-desa">
                            <option value="">Pilih Nama Desa</option>
                            @foreach ($desaList as $item)
                                <option value="{{ $item['kode_desa'] }}"
                                    @selected(($desa['kode_desa'] ?? '') === $item['kode_desa'])>
                                    {{ $item['nama_desa'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="lokasi-jenis">Jenis Lokasi</label>
                        <select id="lokasi-jenis" name="jenis" class="form-control" required data-testid="lokasi-jenis">
                            <option value="">Pilih Jenis</option>
                            @foreach ($point as $item)
                                <option value="{{ $item['id'] }}">{{ $item['nama'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="lokasi-kategori">Kategori Lokasi</label>
                        <select id="lokasi-kategori" name="kategori" class="form-control" required
                            data-testid="lokasi-kategori">
                            <option value="">Pilih Kategori</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="lokasi-nama">Nama Lokasi</label>
                        <input type="text" id="lokasi-nama" name="nama" class="form-control" maxlength="50" required
                            data-testid="lokasi-nama">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="lokasi-lat">Latitude</label>
                            <input type="text" id="lokasi-lat" name="lat" class="form-control" required
                                data-testid="lokasi-lat">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="lokasi-lng">Longitude</label>
                            <input type="text" id="lokasi-lng" name="lng" class="form-control" required
                                data-testid="lokasi-lng">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="lokasi-keterangan">Keterangan</label>
                        <textarea id="lokasi-keterangan" name="keterangan" class="form-control" rows="2"
                            data-testid="lokasi-keterangan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm" data-testid="bt-submit-lokasi">
                        <i class="fa fa-check"></i> Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
