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
                @include('presisi.wilayah.filter')
                
                @include('presisi.wilayah.peta')
                
            </div>
        </div>
    </div>

    @include('presisi.wilayah.data-wilayah')

    @include('presisi.wilayah.data-desa')
    

    
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
	}).addTo(map)

    @include('layouts.presisi.peta.index')
    @include('layouts.presisi.peta.style')
    @include('layouts.presisi.wilayah.data')


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

        $(function() {
    var start = moment();
    var end = moment();

    function cb(start, end) {
        $('#reportrange span').html(start.format('D MMMM, YYYY') + ' - ' + end.format('D MMMM, YYYY'));
    }

    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
            'Hari Ini': [moment(), moment()],
            'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
            '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
            'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
            'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: {
            format: 'D MMMM, YYYY',
            applyLabel: 'Terapkan',
            cancelLabel: 'Batal',
            customRangeLabel: 'Rentang Kustom',
            daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            firstDay: 1
        }
    }, cb);

    cb(start, end);
});
});


</script>
@endpush
