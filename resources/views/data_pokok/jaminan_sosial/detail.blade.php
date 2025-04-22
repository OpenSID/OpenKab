@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'DATA')

@section('content_header')
    <h1>Data {{ $data->nama_kepala_keluarga }} ({{ $data->no_kartu_rumah }})</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-12 mb-4">
                            <a href="{{ route('jaminan-sosial') }}"
                                class="btn btn-social btn-info btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i
                                    class="fa fa-arrow-circle-left"></i> Kembali Ke Daftar Data Jaminan Sosial</a>
                        </div>
                    </div>
                    <div class="box-body">
                        <h5><b>Rincian</b></h5>
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
                        <table class="table table-striped" id="detail-jaminanSosial">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>NIK</th>
                                    <th>NOMOR KK</th>
                                    <th>NAMA</th>
                                    <th>JENIS BANTUAN SOSIAL<br> YANG PERNAH DITERIMA</th>
                                    <th>JENIS GANGGUAN MENTAL<br> YANG DIDERITA</th>
                                    <th>JENIS PENANGANAN <br>PENDERITA GANGGUAN MENTAL</th>
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
            let transformedIncluded = {};
            const headers = @include('layouts.components.header_bearer_api_gabungan');
            var url = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/data-presisi/jaminan-sosial' }}");
            url.searchParams.set("filter[rtm_id]", "{{ $data->rtm_id }}");
            var jaminanSosial = $('#detail-jaminanSosial').DataTable({
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
                            'include': 'rtm,penduduk',
                            "filter[search]": row.search.value,
                            "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]
                                ?.column]?.name,
                        };
                    },
                    dataSrc: function(json) {
                        json.recordsTotal = json.meta.pagination.total;
                        json.recordsFiltered = json.meta.pagination.total;
                        // Transform the included array into an object
                        transformedIncluded = json.included.reduce((acc, item) => {
                            if (!acc[item.type]) {
                                acc[item.type] = {};
                            }
                            acc[item.type][item.id] = item.attributes;
                            return acc;
                        }, {});
                        json.data.forEach(function(item, index) {
                            item.attributes.nik = transformedIncluded.penduduk[item
                                .relationships.penduduk.data.id].nik;
                            item.attributes.nama = transformedIncluded.penduduk[item
                                .relationships.penduduk.data.id].nama;
                            item.attributes.no_kk = transformedIncluded.penduduk[item
                                .relationships.penduduk.data.id].keluarga?.no_kk;
                            if (!item.attributes.jns_bantuan) {
                                item.attributes.jns_bantuan = 'TIDAK TAHU'
                            }
                            if (!item.attributes.jns_gangguan_mental) {
                                item.attributes.jns_gangguan_mental = 'TIDAK TAHU'
                            }
                            if (!item.attributes.terapi_gangguan_mental) {
                                item.attributes.terapi_gangguan_mental = 'TIDAK TAHU'
                            }
                        })
                        return json.data;
                    },
                },
                columnDefs: [{
                    targets: '_all',
                    className: 'text-nowrap',
                }, {
                    targets: [0, 5],
                    orderable: false,
                    searchable: false,
                }],
                columns: [{
                        data: null
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
                        data: 'attributes.jns_bantuan',
                        orderable: false,
                    },
                    {
                        data: 'attributes.jns_gangguan_mental',
                        orderable: false,
                    },
                    {
                        data: 'attributes.terapi_gangguan_mental',
                        orderable: false,
                    },
                ],
            });
            jaminanSosial.on('draw.dt', function() {
                var PageInfo = $('#detail-jaminanSosial').DataTable().page.info();
                jaminanSosial.column(0, {
                    page: 'current'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + PageInfo.start;
                });
            });
        });
    </script>
@endsection
