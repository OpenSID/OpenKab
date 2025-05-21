<div class="row" id="summary_block">
    @include('presisi.summary')
</div>
@push('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            $('#summary_block').change(function(event) {
                let kabupaten = $('#filter_kabupaten').val();
                let kecamatan = $('#filter_kecamatan').val();
                let desa = $('#filter_desa').val();
                let url = "{{ config('app.databaseGabunganUrl') . '/api/v1/data-website' }}";
                if (kabupaten || kecamatan || desa) {
                    url +=
                        `?filter[kode_kabupaten]=${kabupaten || ''}&filter[kode_kecamatan]=${kecamatan || ''}&filter[kode_desa]=${desa || ''}`;
                }
                $.get(url, {}, function(result) {
                    let category = result.data.categoriesItems;
                    let listDesa = result.data.listDesa;
                    let listKecamatan = result.data.listKecamatan;

                    for (let index in category) {
                        $(`.kategori-item .jumlah-${index}-elm`).text(category[index]['value']);
                        $(`.kategori-item .jumlah-${index}-elm`).closest('.btn-detail').data(
                            'filter', {
                                'kode_kabupaten': kabupaten,
                                'kode_kecamatan': kecamatan,
                                'kode_desa': desa,
                                'nama_kabupaten': $('#filter_kabupaten').find(':selected')
                                    .text(),
                                'nama_kecamatan': $('#filter_kecamatan').find(':selected')
                                    .text(),
                                'nama_desa': $('#filter_desa').find(':selected').text(),
                            });
                    }
                }, 'json');
            })
            $('#summary_block').trigger('change');

            $('#summary_block .btn-detail').click(function(event) {
                event.preventDefault();
                let url = $(this).data('url');
                let _url = new URL(url);
                _url.searchParams.set('filter[kode_kabupaten]', $(this).data('filter')['kode_kabupaten']);
                _url.searchParams.set('filter[kode_kecamatan]', $(this).data('filter')['kode_kecamatan']);
                _url.searchParams.set('filter[kode_desa]', $(this).data('filter')['kode_desa']);
                _url.searchParams.set('filter[nama_kabupaten]', $(this).data('filter')['nama_kabupaten']);
                _url.searchParams.set('filter[nama_kecamatan]', $(this).data('filter')['nama_kecamatan']);
                _url.searchParams.set('filter[nama_desa]', $(this).data('filter')['nama_desa']);
                window.location.href = _url.href;
            })
        })
    </script>
@endpush
