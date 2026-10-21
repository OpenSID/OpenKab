{{--
    Layar terbitan skema (KAB-03 / US1-US4).

    Render sebagai DIALOG di atas halaman `dataspasial/skema`, dipicu tombol
    `#btn-terbitan` milik KAB-02 (clarify 2026-10-16 Q1 -> A, research.md R16).
    Tidak ada route/menu baru.

    Isi layar:
      1. Ringkasan perubahan (isian baru / diubah / diarsipkan)   - US1/FR-010
      2. Peringatan dampak pengarsipan + bentrok lokal desa       - US3/FR-040
      3. Panel riwayat versi + perbandingan dua versi             - US2/FR-050
      4. Tabel status adopsi desa (sektor terpilih)              - US4/FR-047

    Kolom catatan perubahan berada di blok bawah (FR-017/FR-018).
--}}
<div class="modal fade" id="layar-terbitan" tabindex="-1" role="dialog" aria-labelledby="layar-terbitan-title" aria-hidden="true" data-testid="layar-terbitan">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="layar-terbitan-title">
                    Terbitkan Skema<span class="ml-2 small text-muted" data-testid="terbitan-sektor"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="text-center py-4" data-testid="terbitan-memuat">
                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status" aria-hidden="true"></div>
                    <p class="text-muted mb-0">Menyiapkan ringkasan perubahan</p>
                </div>

                <div class="d-none" data-testid="terbitan-isi">
                    <div class="alert alert-warning d-none" data-testid="terbitan-gagal" role="alert"></div>

                    <div class="card card-outline card-primary mb-3">
                        <div class="card-header py-2">
                            <h3 class="card-title">Ringkasan Perubahan</h3>
                            <div class="card-tools small text-muted" data-testid="ringkasan-versi"></div>
                        </div>
                        <div class="card-body p-0" data-testid="ringkasan-perubahan"></div>
                    </div>

                    <div class="card card-outline card-warning mb-3 d-none" data-testid="peringatan-dampak">
                        <div class="card-header py-2">
                            <h3 class="card-title">Dampak Pengarsipan Isian</h3>
                        </div>
                        <div class="card-body py-2" data-testid="peringatan-dampak-isi"></div>
                    </div>

                    <div class="card card-outline card-info mb-3">
                        <div class="card-header py-2">
                            <h3 class="card-title">Riwayat Versi</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-outline-info d-none" id="btn-bandingkan-versi" data-testid="btn-bandingkan-versi">
                                    <i class="fas fa-columns mr-1"></i>Bandingkan
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0" data-testid="riwayat-versi"></div>
                        <div class="card-body p-0 border-top d-none" data-testid="bandingkan-versi"></div>
                    </div>

                    <div class="card card-outline card-secondary mb-3">
                        <div class="card-header py-2">
                            <h3 class="card-title">Status Adopsi Desa</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-ekspor-tertinggal" data-testid="btn-ekspor-tertinggal">
                                    <i class="fas fa-file-excel mr-1"></i>Ekspor daftar tertinggal
                                </button>
                            </div>
                        </div>
                        <div class="card-body py-2">
                            <div class="form-row align-items-center">
                                <div class="col-auto">
                                    <label class="mb-0 small" for="adopsi-status">Status</label>
                                    <select class="form-control form-control-sm" id="adopsi-status" data-testid="adopsi-status"></select>
                                </div>
                                <div class="col-auto">
                                    <label class="mb-0 small" for="adopsi-cari">Cari desa</label>
                                    <input type="search" class="form-control form-control-sm" id="adopsi-cari"
                                           placeholder="nama atau kode desa" data-testid="adopsi-cari">
                                </div>
                            </div>
                            <div class="small text-muted mt-2" data-testid="adopsi-ringkasan"></div>
                        </div>
                        <div class="card-body p-0" data-testid="tabel-adopsi"></div>
                        <div class="card-footer py-1 bg-white d-none" data-testid="adopsi-navigasi"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer py-2 d-none flex-wrap" data-testid="terbitan-footer">
                <div class="form-group mb-0 mr-auto text-left flex-grow-1">
                    <label class="small mb-1" for="catatan-perubahan">
                        Catatan perubahan <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control form-control-sm" id="catatan-perubahan"
                           maxlength="255" data-testid="catatan-perubahan"
                           placeholder="Contoh: tambah isian jumlah_siswa, arsipkan lebar_jalan">
                    <div class="invalid-feedback" data-testid="catatan-perubahan-error"></div>
                </div>

                <div class="form-check mr-3 d-none" data-testid="pengakuan-bentrok-wrap">
                    <input class="form-check-input" type="checkbox" id="pengakuan-bentrok" data-testid="pengakuan-bentrok">
                    <label class="form-check-label small" for="pengakuan-bentrok">
                        Saya sudah membaca peringatan bentrok
                    </label>
                </div>

                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-testid="btn-batal-terbit">
                    Batal
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-kirim-terbit" data-testid="btn-kirim-terbit" disabled>
                    <i class="fas fa-paper-plane mr-1"></i>Terbitkan
                </button>
            </div>
        </div>
    </div>
</div>
