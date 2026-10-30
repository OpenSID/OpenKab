<div class="modal fade" id="modal-import" tabindex="-1" role="dialog" aria-labelledby="modal-import-label"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-import" method="POST" action="{{ route('plan.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-import-label">Import Data Lokasi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        Alur: pilih jenis &amp; kategori &rarr; unduh template &rarr; pilih <strong>Nama Desa</strong> dan
                        isi data pada template &rarr; unggah template &rarr; klik Import.
                    </div>

                    <div class="form-group">
                        <label for="import-jenis">Jenis Lokasi</label>
                        <select id="import-jenis" name="jenis" class="form-control" required data-testid="import-jenis">
                            <option value="">Pilih Jenis</option>
                            @foreach ($point as $item)
                                <option value="{{ $item['id'] }}">{{ $item['nama'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="import-kategori">Kategori Lokasi</label>
                        <select id="import-kategori" name="kategori" class="form-control" required
                            data-testid="import-kategori">
                            <option value="">Pilih Kategori</option>
                        </select>
                    </div>

                    <button type="button" class="btn btn-primary btn-sm" id="bt-download-template"
                        data-testid="bt-download-template">
                        <i class="fas fa-download"></i> Download Template
                    </button>

                    <hr>

                    <div class="form-group">
                        <label for="import-file">File Template</label>
                        <input type="file" id="import-file" name="file" class="form-control" accept=".xlsx,.xls"
                            required data-testid="import-file">
                        <small class="text-muted">Pilih desa pada kolom "Nama Desa" di dalam file template. Kode desa
                            ditentukan otomatis oleh sistem.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm" data-testid="bt-submit-import">
                        <i class="fas fa-file-import"></i> Import
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
