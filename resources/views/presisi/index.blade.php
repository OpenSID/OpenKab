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
            <div id="map" style="height: 250px;"></div>
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
                  <span id="summary-luas_wilayah">0</span> Ha
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                Total Lahan Pertanian
                  <span id="summary-luas_pertanian">0</span> Ha
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
              Total Lahan Perkebunan
                  <span id="summary-luas_perkebunan">0</span> Ha
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
              Total Lahan Kehutanan
                  <span id="summary-luas_hutan">0</span> Ha
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
              Total Lahan Peternakan
                  <span id="summary-luas_peternakan">0</span> Ha
              </li>
          </ul>
          </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card rounded-0 elevation-0">
          <div class="card-header">Data Kelurahan /Desa</div>  
          <div class="card-body">
              <div class="table-responsive">
                  <table class="table table-striped" id="summary-penduduk">
                      <thead>
                          <tr>
                              <th class="padat">No</th>
                              <th>Desa</th>
                              <th>Kecamatan</th>
                              <th class="padat">Jumlah Penduduk</th>
                          </tr>
                      </thead>
                      <tbody></tbody>
                  </table>
              </div>          
          </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endpush
@push('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script nonce="{{ csp_nonce() }}" type="text/javascript">
document.addEventListener("DOMContentLoaded", function (event) {
    "use strict";
    const position = [{{ env('LATTITUDE_MAP', -8.459556) }}, {{ env('LONGITUDE_MAP', 115.046600) }}]
    const map = L.map('map').setView( position, 13);
    const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
	}).addTo(map);

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
    const indexSearch = {'search' : {'luas_wilayah' : 1, 'luas_pertanian' : 1, 'luas_perkebunan' : 1, 'luas_hutan' : 1, 'luas_peternakan' : 1}}
    $.get('{{ url('api/v1/data-summary') }}', indexSearch, function(result){
        for(let i in result.data){
            $(`#summary-${i}`).text(result.data[i])                        
        }        
    }, 'json')


    var summaryPenduduk = $('#summary-penduduk').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: false,
            searchPanes: {
                viewTotal: false,
                columns: [0]
            },
            ajax: {
                url: `{{ url('api/v1/wilayah/penduduk') }}`,
                method: 'get',
                data: function(row) {
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[search]": row.search.value,                                            
                    };
                },
                dataSrc: function(json) {
                    json.recordsTotal = json.meta.pagination.total
                    json.recordsFiltered = json.meta.pagination.total

                    return json.data
                },
            },
            columnDefs: [{
                    targets: '_all',
                    className: 'text-nowrap',
                },
                {
                    targets: 0,
                    render: function(data, type, row, meta) {
                        var PageInfo = $('#summary-penduduk').DataTable().page.info();
                        return PageInfo.start + meta.row + 1;
                    }
                },
                {
                    targets: [0, 1, 2, 3],
                    orderable: false,
                    searchable: false,
                },
            ],
            columns: [{
                    data: null,
                },
                {
                    data: "attributes.nama_desa",
                    name: "nama_desa"
                },
                {
                    data: "attributes.nama_kecamatan",
                    name: "nama_kecamatan"
                },                
                {
                    data: "attributes.penduduk_count",
                    name: "penduduk_count",
                    className: 'text-center'
                },
            ],
        })
});
</script>
@endpush
