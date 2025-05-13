<div class="row" id="summary_block">
    <div class="row container d-md-block d-lg-none">
        @php
            $colors = ['primary', 'warning', 'success', 'info']; // Array warna
        @endphp

        @foreach ($categoriesItems as $item)
            @include('dasbor.category_item', $item)
        @endforeach
    </div>

    <div class="btn-group bg-c2 d-md-none d-lg-block">
        <button type="button" class="btn bg-white cbg-white mr-1 rounded-0">
            <div class="info-box-content text-center kategori-item text-primary rounded-circle c-badge">
                <h4><i class="fa-solid fa-chart-column"></i></h4>
            </div>
        </button>
        @php
            $colors = ['primary', 'warning', 'success', 'info']; // Array warna
        @endphp

        @foreach ($categoriesItems as $item)
            @include('dasbor.category_item', $item)
        @endforeach
    </div>
</div>
@push('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            $('#summary_block').change(function(event) {
                let kabupaten = $('#filter_kabupaten').val();
                let kecamatan = $('#filter_kecamatan').val();
                let desa = $('#filter_desa').val();
                let url = "{{ url('api/v1/data-website') }}";
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
                    }

                    let _optionKecamatan = [];
                    let _optionDesa = [];
                    for (let item in listKecamatan) {
                        _optionKecamatan.push(`<option>${item}</option>`);
                    }

                    for (let item in listDesa) {
                        _optionDesa.push(`<optgroup label='${item}'>`);
                        for (let desa in listDesa[item]) {
                            _optionDesa.push(
                                `<option value='${desa}'>${listDesa[item][desa]}</option>`);
                        }
                        _optionDesa.push(`</optgroup>`);
                    }

                    $('select[name=search_kecamatan]').empty().append(_optionKecamatan.join(''))
                        .trigger(
                            "change");
                    $('select[name=search_desa]').empty().append(_optionDesa.join('')).trigger(
                        "change");
                }, 'json');
            })
            $('#summary_block').trigger('change');
        })
    </script>
@endpush
