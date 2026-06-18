@extends('layouts.index')

@section('title', $title)

@section('content_header')
<h1>{{ $title }}</h1>
@stop

@section('content')
@include('partials.breadcrumbs')

<div class="row">
    <div class="col-lg-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <div class="row">
                    <x-filter-tahun :selectedYear="request('tahun')" />
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="detail-kesehatan">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>NIK</th>
                                <th>NOMOR KK</th>
                                <th>NAMA</th>
                                <th>JNS ASURANSI</th>
                                <th>JNS PENGGUNAAN ALAT KONTRASEPSI</th>
                                <th>JNS PENYAKIT YANG DIDERITA</th>
                                <th>KUNJUNGAN KE FASKES DALAM 1 TAHUN</th>
                                <th>RAWAT INAP DALAM 1 TAHUN</th>
                                <th>KUNJUNGAN KE DOKTER DALAM 1 TAHUN</th>
                                <th>KONDISI FISIK SEJAK LAHIR</th>
                                <th>STATUS GIZI BALITA</th>
                                <th>TANGGAL PENGISIAN</th>
                                <th>STATUS PENGISIAN</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script nonce="{{ csp_nonce() }}">
document.addEventListener("DOMContentLoaded", function(event) {
const headers = @include('layouts.components.header_bearer_api_gabungan');
var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/data-presisi/kesehatan' }}");
var colomn = '{{ $colomn }}';

const filterTahun = $('#filter-tahun');
const detailKesehatan = $('#detail-kesehatan');

var kesehatan = detailKesehatan.DataTable({
processing: true,
serverSide: true,
autoWidth: false,
ordering: true,
ajax: {
url: url.href,
headers: headers,
method: 'get',
data: function(row) {
const data = {
"page[size]": row.length,
"page[number]": (row.start / row.length) + 1,
"filter[search]": row.search.value,
"filter[tahun]": filterTahun.val(),
"filter[colomn]": colomn,
"sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]?.name,
};

return data;
},
dataSrc: function(json) {
json.recordsTotal = json.meta.pagination.total;
json.recordsFiltered = json.meta.pagination.total;
return json.data;
},
},
columnDefs: [{
targets: '_all',
className: 'text-nowrap',
}, {
targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
orderable: false,
searchable: false,
}],
columns: [{
data: null,
orderable: false,
render: function(data, type, row, meta) {
return meta.row + meta.settings._iDisplayStart + 1;
}
},
{
data: 'attributes.nik',
orderable: false,
},
{
data: 'attributes.no_kk',
orderable: false,
},
{
data: 'attributes.nama',
orderable: false,
},
{
data: 'attributes.jns_ansuransi',
orderable: false
},
{
data: 'attributes.jns_penggunaan_alat_kontrasepsi',
orderable: false
},
{
data: 'attributes.jns_penyakit_diderita',
orderable: false
},
{
data: 'attributes.frekwensi_kunjungan_faskes_pertahun',
orderable: false
},
{
data: 'attributes.frekwensi_rawat_inap_pertahun',
orderable: false
},
{
data: 'attributes.frekwensi_kunjungan_dokter_pertahun',
orderable: false
},
{
data: 'attributes.kondisi_fisik_sejak_lahir',
orderable: false
},
{
data: 'attributes.status_gizi_balita',
orderable: false
},
{
data: 'attributes.tanggal_pengisian',
orderable: false
},
{
data: 'attributes.status_pengisian',
orderable: false
},
],
});

kesehatan.on('draw.dt', function() {
var PageInfo = detailKesehatan.DataTable().page.info();
kesehatan.column(0, {
page: 'current'
}).nodes().each(function(cell, i) {
cell.innerHTML = i + 1 + PageInfo.start;
});
});

filterTahun.on('change', function() {
kesehatan.ajax.reload();
});
});
</script>
@endsection