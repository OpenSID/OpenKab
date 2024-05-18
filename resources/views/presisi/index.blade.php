@extends('layouts.presisi')

@section('content_header')
    <h1>Dashboard Data Presisi</h1>
@stop
@section('content')

@include('presisi.summary')

@endsection

@push('scripts')
<script nonce="{{ csp_nonce() }}" type="text/javascript">
document.addEventListener("DOMContentLoaded", function (event) {
    "use strict";
    $.get('{{ url('index.php/api/v1/data-website') }}', {}, function(result){
        let category = result.data.categoriesItems
        let listDesa = result.data.listDesa
        let listKecamatan = result.data.listKecamatan

        for(let index  in category) {
            $(`.kategori-item .jumlah-${index}-elm`).text(category[index]['value'])
        };
        let _optionKecamatan = []
        let _optionDesa = []
        for(let item in listKecamatan){
            _optionKecamatan.push(`<option>${item}</option>`)
        }

        for(let item in listDesa){
            _optionDesa.push(`<optgroup label='${item}'>`)
            for(let desa in listDesa[item]){
                _optionDesa.push(`<option value='${desa}'>${listDesa[item][desa]}</option>`)
            }
            _optionDesa.push(`</optgroup>`)
            _optionKecamatan.push(`<option>${item}</option>`)
        }

        $('select[name=search_kecamatan]').append(_optionKecamatan.join(''))
        $('select[name=search_desa]').append(_optionDesa.join(''))
    }, 'json')
});
</script>
@endpush
