@extends('layouts.web')

@section('content')
<!-- Header Start -->
<div class="container-fluid header bg-white p-0">
    <div class="row g-0 align-items-center flex-column-reverse flex-md-row">
        @include('web.partials.slider')
    </div>
</div>
<!-- Header End -->


<!-- Search Start -->
<div class="container-fluid bg-primary mb-5 wow fadeIn p-3" data-wow-delay="0.1s">
    <div class="container">
        @include('web.partials.summary')
        @include('web.partials.search', ['listKabupaten' => $listKabupaten,'listKecamatan' => $listKecamatan, 'listDesa' => $listDesa])
    </div>
</div>
<!-- Search End -->


<!-- Category Start -->
<div class="container-xxl py-5">
    @include('web.partials.statistik')
</div>
<!-- Category End -->

<!-- Property List Start -->
<div class="container-xxl py-5">
    @include('web.partials.property')
</div>
<!-- Property List End -->

<!-- Team Start -->
<div class="container-xxl py-5">
    @include('web.partials.team')
</div>
<!-- Team End -->
@endsection

@push('scripts')
<script nonce="{{ csp_nonce() }}" type="text/javascript">
document.addEventListener("DOMContentLoaded", function (event) {
    "use strict";

    const header = @include('layouts.components.header_bearer_api_gabungan');
    var urlDataWebsite = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/data-website' }}");

    $.ajax({
        url: urlDataWebsite.href,
        method: 'GET',
        dataType: 'json',
        headers: header,
        success: function(result) {
            let category = result.data.categoriesItems;
            let listDesa = result.data.listDesa;
            let listKecamatan = result.data.listKecamatan;
            let listKabupaten = result.data.listKabupaten;

            for (let index in category) {
                $(`.kategori-item .jumlah-${index}-elm`).text(category[index]['value']);
            }

            let _optionKabupaten = [];
            let _optionKecamatan = [];
            let _optionDesa = [];

            for (let item in listKabupaten) {
                _optionKabupaten.push(`<option>${item}</option>`);
            }

            for (let item in listKecamatan) {
                _optionKecamatan.push(`<optgroup label='${item}'>`);
                for (let kecamatan in listKecamatan[item]) {
                    _optionKecamatan.push(`<option value='${listKecamatan[item][kecamatan]}'>${listKecamatan[item][kecamatan]}</option>`);
                }
                _optionKecamatan.push(`</optgroup>`);
            }

            for (let item in listDesa) {
                _optionDesa.push(`<optgroup label='${item}'>`);
                for (let desa in listDesa[item]) {
                    _optionDesa.push(`<option value='${desa}'>${listDesa[item][desa]}</option>`);
                }
                _optionDesa.push(`</optgroup>`);
            }

            $('select[name=search_kabupaten]').append(_optionKabupaten.join(''));
            $('select[name=search_kecamatan]').append(_optionKecamatan.join(''));
            $('select[name=search_desa]').append(_optionDesa.join(''));
        }
    });

    // $.get('{{ url('index.php/api/v1/data-website') }}', {}, function(result){

    //     let category = result.data.categoriesItems
    //     let listDesa = result.data.listDesa
    //     let listKecamatan = result.data.listKecamatan
    //     let listKabupaten = result.data.listKabupaten

    //     for(let index  in category) {
    //         $(`.kategori-item .jumlah-${index}-elm`).text(category[index]['value'])
    //     };
    //     let _optionKabupaten = []
    //     let _optionKecamatan = []
    //     let _optionDesa = []

    //     for(let item in listKabupaten){
    //     _optionKabupaten.push(`<option>${item}</option>`)
    //     }

    //     for(let item in listKecamatan){
    //     _optionKecamatan.push(`<optgroup label='${item}'>`)
    //         for(let kecamatan in listKecamatan[item]){
    //         _optionKecamatan.push(`<option value='${listKecamatan[item][kecamatan]}'>${listKecamatan[item][kecamatan]}</option>`)
    //         }
    //         _optionKecamatan.push(`</optgroup>`)
    //     }

    //     for(let item in listDesa){
    //         _optionDesa.push(`<optgroup label='${item}'>`)
    //         for(let desa in listDesa[item]){
    //             _optionDesa.push(`<option value='${desa}'>${listDesa[item][desa]}</option>`)
    //         }
    //         _optionDesa.push(`</optgroup>`)
    //     }

    //     $('select[name=search_kabupaten]').append(_optionKabupaten.join(''))
    //     $('select[name=search_kecamatan]').append(_optionKecamatan.join(''))
    //     $('select[name=search_desa]').append(_optionDesa.join(''))
    // }, 'json')
});
</script>
@endpush
