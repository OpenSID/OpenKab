<div class="container">
    <div class="row g-0 gx-5 align-items-end">
        <div class="col-lg-6">
            <div class="text-start mx-auto mb-5 wow slideInUp" data-wow-delay="0.1s">
                <h1 class="mb-3">Artikel Terbaru</h1>
                <p>Berita dan informasi terbaru seputar {{ config('app.sebutanKab') }}</p>
            </div>
        </div>
        <div class="col-lg-6 text-start text-lg-end wow slideInUp" data-wow-delay="0.1s">
            <a href="{{ route('web.artikel.index') }}" class="btn btn-primary px-4 py-2 mb-5">Lihat Semua Artikel</a>
        </div>
    </div>
    <div class="tab-content">
        <div id="tab-artikel" class="tab-pane fade show p-0 active">
            <div class="row g-4 replace-content-artikel">
                <!-- Skeleton Loader -->
                @for ($i = 0; $i < 6; $i++)
                    <div class="col-lg-4 col-md-6 wow fadeInUp skeleton-artikel" data-wow-delay="0.1s">
                        <div class="card shadow-sm h-100 placeholder-glow">
                            <div class="placeholder border-0 bg-secondary" style="height: 225px; width: 100%;"></div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title placeholder-glow mb-2">
                                    <span class="placeholder col-8"></span>
                                </h5>
                                <div class="mb-2 placeholder-glow">
                                    <span class="placeholder col-4 bg-primary rounded"></span>
                                </div>
                                <p class="card-text placeholder-glow flex-grow-1 mt-2">
                                    <span class="placeholder col-12"></span>
                                    <span class="placeholder col-10"></span>
                                    <span class="placeholder col-8"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script nonce="{{ csp_nonce() }}" type="text/javascript">
        document.addEventListener("DOMContentLoaded", function (event) {
            "use strict";

            const urlArtikel = "{{ route('web.artikel.terbaru') }}";

            $.ajax({
                url: urlArtikel,
                method: 'GET',
                dataType: 'json',
                data: {
                    limit: 6
                },
                success: function (result) {
                    if (result.data && result.data.length > 0) {
                        let htmlContent = '';

                        result.data.forEach((item) => {
                            // Use dummy image if none provided
                            let imgSrc = item.gambar ? item.gambar : `data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22100%25%22%20height%3D%22225%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20100%20225%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_18e19c362b5%20text%20%7B%20fill%3A%23eceeef%3Bfont-weight%3Abold%3Bfont-family%3AArial%2C%20Helvetica%2C%20Open%20Sans%2C%20sans-serif%2C%20monospace%3Bfont-size%3A11pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_18e19c362b5%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22225%22%20fill%3D%22%2355595c%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%3EThumbnail%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E`;

                            // Strip HTML tags from isi
                            let tmp = document.createElement("DIV");
                            tmp.innerHTML = item.isi || '';
                            let textLength = 100;
                            let plainText = tmp.textContent || tmp.innerText || "";
                            let isiExcerpt = plainText.length > textLength ? plainText.substring(0, textLength) + "..." : plainText;

                            // Format date simple
                            let dateStr = item.tgl_upload ? item.tgl_upload.split(' ')[0] : '';
                            let detailUrl = item.detail_url || '#';
                            let kategori = item.kategori_nama || 'Kategori';
                            let badgeSource = item.source === 'openkab' ? '<span class="badge bg-success me-1">OpenKab</span>' : '';

                            htmlContent += `
                                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                                        <div class="card shadow-sm h-100">
                                            <img src="${imgSrc}" width="100%" height="225" class="card-img-top object-fit-cover" alt="${item.judul || ''}">
                                            <div class="card-body d-flex flex-column">
                                                <h5 class="card-title text-truncate">${item.judul || ''}</h5>
                                                <div class="mb-2">
                                                    ${badgeSource}
                                                    <span class="badge bg-primary">${kategori}</span>
                                                </div>
                                                <div class="card-text flex-grow-1" style="font-size: 0.9rem;">
                                                    ${isiExcerpt}
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-3">
                                                    <a href="${detailUrl}" class="text-decoration-none btn btn-sm btn-outline-primary">Selengkapnya</a>
                                                    <small class="text-muted"><i class="far fa-calendar-alt me-1"></i>${dateStr}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                        });

                        $('.replace-content-artikel').html(htmlContent);
                    } else {
                        $('.replace-content-artikel').html(`
                                <div class="col-12 text-center text-muted py-4">
                                    <i>Belum ada artikel terbaru</i>
                                </div>
                            `);
                    }
                },
                error: function () {
                    $('.replace-content-artikel').html(`
                            <div class="col-12 text-center text-danger py-4">
                                <i>Gagal memuat artikel terbaru</i>
                            </div>
                        `);
                }
            });
        });
    </script>
@endpush