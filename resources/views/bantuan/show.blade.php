@extends('layouts.index')

@section('title', 'Data Peserta Bantuan')

@section('content_header')
    <h1>Data Peserta Bantuan</h1>
@stop

@section('content')
    <div class="row" id="tampilkan-bantuan">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ url('bantuan') }}" class="btn btn-primary">Kembali ke Daftar Bantuan</a>
                </div>
                <div class="card-body">
                    <h5><b>Rincian Program</b></h5>
                    <div class="table-responsive" id="bantuan-detail">
                    </div>
                    <br>
                    <h5><b>Daftar Peserta</b></h5>
                    <div class="table-responsive">
                        <table class="table table-striped" id="peserta">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>No. KK</th>
                                    <th>Nama Penduduk</th>
                                    <th>No. Kartu Peserta</th>
                                    <th>Tempat Lahir</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Alamat</th>
                                    <th>Keterangan</th>
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
    <script>
        var nama_desa = `{{ session('desa.nama_desa') }}`;

        $.ajax({
            url: `{{ url('api/v1/bantuan') }}?filter[id]={{ $id }}`,
            method: 'get',
            success: function(response) {
                if (response.data[0].length == 0) {
                    $('#tampilkan-bantuan').html(`
                        <div class="col-lg-12">
                            <div class="alert alert-warning">
                                <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                                Tidak ada data bantuan yang tersedia untuk Desa ${nama_desa}.
                            </div>
                        </div>
                    `)
                }

                var bantuan = response.data[0]
                var html = ''

                html += `
                    <table class="table table-bordered table-striped table-hover">
                        <tbody>
                            <tr>
                                <td width="20%">Nama Program</td>
                                <td width="1">:</td>
                                <td>${bantuan.attributes.nama}</td>
                            </tr>
                            <tr>
                                <td>Sasaran Peserta</td>
                                <td> : </td>
                                <td>${bantuan.attributes.nama_sasaran}</td>
                            </tr>
                            <tr>
                                <td>Masa Berlaku</td>
                                <td> : </td>
                                <td>${bantuan.attributes.sdate} - ${bantuan.attributes.edate}</td>
                            </tr>
                            <tr>
                                <td>Keterangan</td>
                                <td> : </td>
                                <td>${bantuan.attributes.ndesc}</td>
                            </tr>
                        </tbody>
                    </table>
                `

                $('#bantuan-detail').html(html)
            }
        });

        var peserta = $('#peserta').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            ajax: {
                url: `{{ url('api/v1/bantuan/peserta') }}?filter[program_id]={{ $id }}`,
                method: 'get',
                data: function(row) {
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[search]": row.search.value,
                        "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]
                            ?.name
                    };
                },
                dataSrc: function(json) {
                    json.recordsTotal = json.meta.pagination.total
                    json.recordsFiltered = json.meta.pagination.total

                    return json.data
                },
            },
            columns: [
                {
                    data: null,
                    searchable: false,
                    orderable: true
                },
                {
                    data: "attributes.nik", name: "nik", searchable: false, orderable: false
                },
                {
                    data: "attributes.no_kk", name: "no_kk", searchable: false, orderable: false
                },
                {
                    data: "attributes.kartu_nama", name: "kartu_nama"
                },
                {
                    data: "attributes.no_id_kartu", name: "no_id_kartu"
                },
                {
                    data: "attributes.kartu_tempat_lahir", name: "kartu_tempat_lahir", searchable: false, orderable: false
                },
                {
                    data: "attributes.kartu_tanggal_lahir", name: "kartu_tanggal_lahir", searchable: false, orderable: false
                },
                {
                    data: "attributes.kartu_alamat", name: "kartu_alamat", searchable: false, orderable: false
                },
                {
                    data: "attributes.jenis_kelamin.nama", name: "jenis_kelamin", searchable: false, orderable: false
                },
                {
                    data: "attributes.keterangan.nama", name: "keterangan", searchable: false, orderable: false
                },
            ],
            order: [[ 3, 'asc' ]]
        })

        peserta.on('draw.dt', function() {
            var PageInfo = $('#peserta').DataTable().page.info();
            peserta.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });
    </script>
@endsection
