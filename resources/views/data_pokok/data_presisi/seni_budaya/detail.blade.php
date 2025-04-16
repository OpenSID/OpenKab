@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'DATA')

@section('content_header')
    <h1>Data {{ $data->nama_kepala_keluarga }} ({{$data->no_kartu_rumah}})</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-12 mb-4">
                            <a href="{{ route('data-pokok.data-presisi-seni-budaya.index') }}" class="btn btn-social btn-info btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i class="fa fa-arrow-circle-left"></i> Kembali Ke Daftar Data Seni Budaya</a>
                        </div>
                    </div>
                    <div class="box-body">
                        <h5><b>Rincian Suplemen</b></h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover tabel-rincian">
                                <tbody>
                                    <tr>
                                        <td width="20%">No Kartu Rumah Tangga (KRT)</td>
                                        <td width="1%">:</td>
                                        <td>{{ $data->no_kartu_rumah }}</td>
                                    </tr>
                                    <tr>
                                        <td>Kepala Rumah Tangga</td>
                                        <td>:</td>
                                        <td>{{ $data->nama_kepala_keluarga }}</td>
                                    </tr>
                                    <tr>
                                        <td>Alamat</td>
                                        <td>:</td>
                                        <td>{{ $data->alamat }}</td>
                                    </tr>
                                    <tr>
                                        <td>Jumalah Anggota</td>
                                        <td>:</td>
                                        <td>{{ $data->jumlah_anggota }}</td>
                                    </tr>
                                    <tr>
                                        <td>Jumlah KK</td>
                                        <td>:</td>
                                        <td>{{ $data->jumlah_kk }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="detail-seni">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>NIK</th>
                                    <th>NOMOR KK</th>
                                    <th>NAMA</th>
                                    <th>JENIS SENI YANG DIKUASAI</th>
                                    <th>JUMLAH PENGHASILAN DARI SENI</th>
                                    <th>TANGGAL PENGISIAN</th>
                                    <th>STATUS PENGISISAN</th>
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
        var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/data-presisi/seni-budaya' }}");
        url.searchParams.set("filter[rtm_id]", "{{ $data->rtm_id }}");
        var seni = $('#detail-seni').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            searchPanes: {
                viewTotal: false,
                columns: [0]
            },
            ajax: {
                url: url.href,
                headers: headers,
                method: 'get',
                data: function(row) {
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[search]": row.search.value,
                        "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]?.name,
                    };
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
                targets: [0, 1, 2, 3, 4, 5],
                orderable: false,
                searchable: false,
            }],
            columns: [
                {
                    data: null,
                    orderable: false,
                    render: function (data, type, row, meta) {
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
                    data: 'attributes.jenis_seni_yang_dikuasai.jenis_seni_value',
                    orderable: false,
                },
                {
                    data: 'attributes.jumlah_penghasilan_dari_seni',
                    orderable: false,
                },
                {
                    data: 'attributes.tanggal_pengisian',
                    orderable: false,
                },
                {
                    data: 'attributes.status_pengisian',
                    orderable: false,
                },
            ]
        });
        seni.on('draw.dt', function() {
            var PageInfo = $('#detail-seni').DataTable().page.info();
            seni.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });
        $('#sex, #dusun, #rw, #rt').change(function() {
            seni.draw();
        });
    });
    </script>
@endsection