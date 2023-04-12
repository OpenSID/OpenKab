@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Bantuan')

@section('content_header')
    <h1>Data Bantuan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="bantuan">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Aksi</th>
                                    <th>Nama Program</th>
                                    <th>Asal Dana</th>
                                    <th>Jumlah Peseerta</th>
                                    <th>Masa Berlaku</th>
                                    <th>Sasaran</th>
                                    <th>Status</th>
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
        var bantuan = $('#bantuan').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            ajax: {
                url: `{{ url('api/v1/bantuan') }}`,
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
            columnDefs: [{
                    targets: [0, 1, 2, 3, 4, 5, 6, 7],
                    className: 'text-nowrap',
                },
                {
                    targets: [0, 1, 4, 5, 6, 7],
                    orderable: false,
                    searchable: false,
                },
            ],
            columns: [{
                    data: null,
                },
                {
                    data: function(data) {
                        return `<a href="{{ url('bantuan') }}/${data.id}">
                                    <button class="btn btn-warning btn-sm">Detail</button>
                                </a>`
                    },
                },
                {
                    data: "attributes.nama",
                    name: "nama"
                },
                {
                    data: "attributes.asaldana",
                    name: "asaldana"
                },
                {
                    data: "attributes.jumlah_peserta",
                    name: "jumlah_peserta",
                    className: 'text-center'
                },
                {
                    data: function(data) {
                        return data.attributes.sdate + ' - ' + data.attributes.edate
                    },
                },
                {
                    data: "attributes.nama_sasaran",
                    name: "nama_sasaran",
                },
                {
                    data: function(data) {
                        return data.attributes.status == 1 ? 'Aktif' : 'Tidak Aktif'
                    },
                },
            ],
            order: [
                [2, 'asc']
            ]
        })

        bantuan.on('draw.dt', function() {
            var PageInfo = $('#bantuan').DataTable().page.info();
            bantuan.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });
    </script>
@endsection
