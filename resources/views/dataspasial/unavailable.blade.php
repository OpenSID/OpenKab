{{--
    Pesan "data sementara tidak tersedia" (FR-014).

    WAJIB generik: tanpa detail teknis, nama endpoint, kode status HTTP,
    atau jejak_exception. Detail tersebut hanya untuk log backend.
--}}
<div class="alert alert-warning border-0 shadow-sm" role="alert" data-testid="dataspasial-unavailable">
    <div class="d-flex align-items-start">
        <div class="mr-3">
            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
        </div>
        <div>
            <h5 class="alert-heading">Data sementara tidak tersedia</h5>
            <p class="mb-0">
                Sumber data sedang tidak dapat diakses. Silakan coba lagi nanti.
                Halaman ini tetap dapat digunakan untuk menu lain.
            </p>
        </div>
    </div>
</div>
