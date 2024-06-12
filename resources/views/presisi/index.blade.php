@extends('layouts.presisi.index')

@section('content_header')
    <h2>Dashboard Data Presisi</h2>
@stop
@section('content')

@include('presisi.summary')
<div class="row">
    <div class="col-md-6">
        <div class="card rounded-0 elevation-0">
            <div class="card-header">Peta</div>
            <div class="card-body">
                
                <img height="245px" src='data:image/jpeg;base64,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'/>
                
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card rounded-0 elevation-0">
          <div class="card-header">Data Batas Wilayah</div>  
          <div class="card-body">
          <ul class="list-group">
              <li class="list-group-item d-flex justify-content-between align-items-center">
                  Total Luas Wilayah
                  <span >14</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                Total Lahan Pertanian
                  <span >2</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
              Total Lahan Perkebunan
                  <span >13</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
              Total Lahan Kehutanan
                  <span >122</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
              Total Lahan Peternakan
                  <span >10</span>
              </li>
          </ul>
          </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card rounded-0 elevation-0">
          <div class="card-header">Data Kelurahan /Desa</div>  
          <div class="card-body">
           
          </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script nonce="{{ csp_nonce() }}" type="text/javascript">
document.addEventListener("DOMContentLoaded", function (event) {
    "use strict";
    $.get('{{ url('api/v1/data-website') }}', {}, function(result){
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
