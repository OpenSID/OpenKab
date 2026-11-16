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
        <div class="mb-2">
            <small class="text-muted">Sektor</small>
            <div class="font-weight-bold" id="nama-sektor-pratinjau">{{ $sektorTerpilih['nama'] ?? 'Belum diisi' }}</div>
        </div>
        <hr class="my-2">
        <div id="wadah-pratinjau" data-testid="wadah-pratinjau"></div>
        <noscript>
            <div class="alert alert-secondary border-0 py-2 px-2 mb-0 small">
                Pratinjau live memerlukan JavaScript.
            </div>
        </noscript>
    </div>
</div>