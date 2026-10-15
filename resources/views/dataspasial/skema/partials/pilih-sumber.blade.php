{{--
    Pemilih sumber nilai untuk pemetaan isian (FR-022/FR-023).

    ⚠️ VERIFIKASI LANGSUNG KE API (§6a.5): `pemetaan[].sumber` adalah **ENUM**
    `openSID` / `dataPresisi` / `impor` — BUKAN kode sektor. Keputusan Q2 ("sumber
    = daftar sektor") TIDAK sesuai implementasi dan dicabut; daftar di bawah
    memakai enum resmi tersebut.

    Isian yang sudah terpetakan ditampilkan sebagai badge pada tabel isian,
    bukan sebagai select — melepaskannya dilakukan lewat tombol di sini.
--}}
<div class="modal fade" id="modal-pilih-sumber" tabindex="-1" role="dialog"
     aria-labelledby="judul-pilih-sumber" aria-hidden="true" data-testid="modal-pilih-sumber">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="judul-pilih-sumber">
                    Pilih sumber nilai untuk
                    <code id="sumber-kode-isian" data-testid="sumber-kode-isian"></code>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-2">
                <p class="text-muted small mb-2">
                    Nilai isian ini akan diambil otomatis dari sumber yang dipilih dan
                    tampil <strong>terkunci</strong> pada formulir desa.
                </p>

                @php $sumberTersedia = ['openSID' => 'OpenSID (data desa)', 'dataPresisi' => 'Data Presisi', 'impor' => 'Data Impor']; @endphp

                <input type="search" class="form-control form-control-sm mb-2"
                       id="cari-sumber" placeholder="Cari sumber..."
                       data-testid="cari-sumber" autocomplete="off">

                <ul class="list-group list-group-flush" id="daftar-sumber"
                    data-testid="daftar-sumber" style="max-height: 18rem; overflow-y: auto;">
                    @foreach ($sumberTersedia as $kode => $label)
                        <li class="list-group-item py-2 px-2 item-sumber"
                            data-sumber="{{ $label }}"
                            data-kode="{{ $kode }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="font-weight-bold">{{ $label }}</div>
                                    <small class="text-muted"><code>{{ $kode }}</code></small>
                                </div>
                                <button type="button" class="btn btn-outline-info btn-sm btn-pilih-sumber"
                                        data-kode="{{ $kode }}" data-nama="{{ $label }}">
                                    <i class="fas fa-link mr-1"></i>Pakai
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="modal-footer py-2">
                {{-- Melepas pemetaan → isian kembali diisi manual (US4#3). --}}
                <button type="button" class="btn btn-outline-secondary btn-sm mr-auto"
                        id="btn-lepas-pemetaan" data-testid="btn-lepas-pemetaan">
                    <i class="fas fa-unlink mr-1"></i>Lepas Pemetaan
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>