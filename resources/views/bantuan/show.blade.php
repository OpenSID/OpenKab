@extends('layouts.index')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/progressive-image/progressive-image.css') }}">
@endpush

@section('title', 'Data Peserta Bantuan')

@section('content_header')
    <h1>Data Peserta Bantuan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ url('bantuan') }}" class="btn btn-primary">Kembali ke Daftar Bantuan</a>
                </div>
                <div class="card-body">
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

@push('js')
    <script src="{{ asset('assets/progressive-image/progressive-image.js') }}"></script>
@endpush

@section('js')
    <script>
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
                        "page[number]": (row.start / row.length) + 1
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
                    data: "attributes.nik", name: "nik"
                },
                {
                    data: "attributes.no_kk", name: "no_kk"
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
                    data: function (data) {
                        return data.attributes.jenis_kelamin == 1 ? 'Laki-laki' : 'Perempuan'
                    },
                    searchable: false,
                    orderable: false
                },
                {
                    data: function (data) {
                        return data.attributes.keterangan == 1 ? 'Hidup' : 'Lainnya'
                    },
                    searchable: false,
                    orderable: false
                },
            ]
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
