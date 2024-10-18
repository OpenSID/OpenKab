@extends('layouts.presisi.index')

@section('content_header')
@stop
@section('content')
    @include('presisi.partials.head')

<div class="row m-1">
    <div class="col-md-12">
        
        <div class="card rounded-0 border-0 shadow-none">
            @include('presisi.summary')
            <div class="card-body">
            <div id="map" style="height: 250px;"></div>
            </div>            
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card p-3 bg-light-oren">
            <table>
                <tr>
                    <td rowspan="2" width="24%"><h4 class="rounded-circle c-badge1 mr-2 text-center"><i class="fas fa-bullseye"></i></h4></td>
                    <td>
                    <h5>
                        <span id="summary-luas_wilayah">0</span> Ha
                    </h5>
                    </td>
                </tr>
                <tr>
                    <td><p class="text-sm text-muted">Luas Wilayah</p></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card p-3 bg-light-green">
            <table>
                <tr>
                    <td rowspan="2" width="24%"><h4 class="rounded-circle c-badge2 mr-2 text-center"><i class="fas fa-check-circle"></i></h4></td>
                    <td><h5><span id="summary-luas_pertanian">0</span> Ha</h5></td>
                </tr>
                <tr>
                    <td><p class="text-sm text-muted">Total Lahan Pertanian</p></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card p-3 bg-light-purple">
            <table>
                <tr>
                    <td rowspan="2" width="24%"><h4 class="rounded-circle c-badge3 mr-2 text-center"><i class="fas fa-clock"></i></h4></td>
                    <td><h5><span id="summary-luas_perkebunan">0</span> Ha</h5></td>
                </tr>
                <tr>
                    <td><p class="text-sm text-muted">Total Lahan Perkebunan</p></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card p-3 bg-light-blue">
            <table>
                <tr>
                    <td rowspan="2" width="24%"><h4 class="rounded-circle c-badge4 mr-2 text-center"><i class="fas fa-dollar-sign"></i></h4></td>
                    <td><h5><span id="summary-luas_hutan">0</span> Ha</h5></td>
                </tr>
                <tr>
                    <td><p class="text-sm text-muted">Total Lahan Kehutanan</p></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card rounded-0 elevation-0">
          <div class="card-header bg-white">Data Kelurahan /Desa</div>  
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

        $('.datepicker').daterangepicker(
            {
                autoApply: true,
                singleDatePicker: true,
                locale: {                    
                    firstDay: 1
                }
            });
            $(function() {

var start = moment();
var end = moment();

function cb(start, end) {
    $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
}

$('#reportrange').daterangepicker({
    startDate: start,
    endDate: end,
    ranges: {
    'Today': [moment(), moment()],
    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
    'This Month': [moment().startOf('month'), moment().endOf('month')],
    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    }
}, cb);

cb(start, end);

});

});
</script>
@endpush
