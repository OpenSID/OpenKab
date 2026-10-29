{{--
    Keadaan kosong (FR-015): respons sukses tanpa data.

    Sengaja BERBEDA dari `unavailable.blade.php` — ini bukan kegagalan,
    melainkan memang belum ada data.
--}}
<div class="card border-0 shadow-sm" data-testid="dataspasial-empty">
    <div class="card-body text-center py-5">
        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
        <h5 class="mb-1">Belum ada data</h5>
        <p class="text-muted mb-0">
            Sumber data dapat diakses, tetapi belum ada isian yang perlu ditampilkan.
        </p>
    </div>
</div>
