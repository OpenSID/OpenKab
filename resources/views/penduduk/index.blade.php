@extends('layouts.index')

@section('title', 'Data Penduduk')

@section('content_header')
    <h1>Data Penduduk</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="penduduk">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Aksi</th>
                                    <th>Foto</th>
                                    <th>NIK</th>
                                    <th>Tag ID Card</th>
                                    <th>Nama</th>
                                    <th>No. KK</th>
                                    <th>Nama Ayah</th>
                                    <th>Nama Ibu</th>
                                    <th>No. Rumah Tangga</th>
                                    <th>Alamat</th>
                                    <th>Dusun</th>
                                    <th>RW</th>
                                    <th>RT</th>
                                    <th>Pendidikan dalam KK</th>
                                    <th>Umur</th>
                                    <th>Pekerjaan</th>
                                    <th>Kawin</th>
                                    <th>Tgl Peristiwa</th>
                                    <th>Tgl Terdaftar</th>
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
        var penduduk = $('#penduduk').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            ajax: {
                url: `{{ url('api/v1/penduduk') }}`,
                method: 'get',
                data: function(row) {
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[nama]": row.search.value,
                        "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]?.name
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
                    orderable: false
                },
                {
                    data: "attributes.nama",
                    searchable: false,
                    orderable: false
                },
                {
                    data: "attributes.foto"
                    searchable: false,
                    orderable: false
                },
                {
                    data: "attributes.nik", name: "nik"
                },
                {
                    data: "attributes.tag_id_card": name: "tag_id_card"
                },
                {
                    data: "attributes.nama": name: "nama"
                },
                {
                    data: function (attributes) {
                        return attributes.keluarga?.no_kk ?? null
                    }
                },
                {
                    data: "attributes.nama"
                },
                {
                    data: "attributes.nama"
                },
                {
                    data: "attributes.nama"
                },
                {
                    data: "attributes.nama"
                },
                {
                    data: "attributes.nama"
                },
                {
                    data: "attributes.nama"
                },
                {
                    data: "attributes.nama"
                },
                {
                    data: "attributes.nama"
                },
                {
                    data: "attributes.nama"
                },
                {
                    data: "attributes.nama"
                },
                {
                    data: "attributes.nama"
                },
                {
                    data: "attributes.nama"
                },
                {
                    data: "attributes.nama"
                }
            ]
        })

        penduduk.on('draw.dt', function() {
            var PageInfo = $('#penduduk').DataTable().page.info();
            penduduk.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });
    </script>
@endsection
