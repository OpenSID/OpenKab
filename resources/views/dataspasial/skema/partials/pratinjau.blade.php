{{--
    Panel kanan: pratinjau formulir desa (FR-034).

    Pratinjau dibangun dari STATE KLIEN yang sama dengan form editor
    (`renderPratinjau(stateIsian)` di `schema-editor.js`) agar perubahan yang
    BELUM disimpan tetap terlihat (FR-035, research.md R7).

    Isian terpetakan tampil terkunci dengan keterangan sumbernya (FR-015).
--}}
<div class="card card-outline card-warning">
    <div class="card-header py-2 px-3">
        <h3 class="card-title text-sm mb-0">
            <i class="fas fa-eye mr-1"></i>Pratinjau Formulir Desa
        </h3>
    </div>

    <div class="card-body p-3">
        @if ($sektorTerpilih === null)
            <p class="text-muted text-center mb-0" data-testid="pratinjau-kosong">
                Pratinjau formulir akan muncul setelah sektor dipilih.
            </p>
        @else
            <div class="mb-2">
                <small class="text-muted">Sektor</small>
                <div class="font-weight-bold">{{ $sektorTerpilih['nama'] }}</div>
                @if ($skemaTerpilih)
                    <small class="text-muted">
                        Versi {{ $skemaTerpilih['nomorVersi'] }}
                        @if ($skemaTerpilih['status'] === 'terbit')
                            <span class="badge badge-success ml-1">Terbit</span>
                        @else
                            <span class="badge badge-warning ml-1">Draf</span>
                        @endif
                    </small>
                @endif
            </div>

            <hr class="my-2">

            {{-- Dikosongkan oleh JS lalu diisi `renderPratinjau()`. --}}
            <div id="wadah-pratinjau" data-testid="wadah-pratinjau"></div>

            <noscript>
                <div class="alert alert-secondary border-0 py-2 px-2 mb-0 small">
                    Pratinjau live memerlukan JavaScript. Tabel isian di samping tetap
                    dapat digunakan.
                </div>
            </noscript>
        @endif
    </div>
</div>