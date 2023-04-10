@extends('layouts.index')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/progressive-image/progressive-image.css') }}">
@endpush

@section('title', 'Data Bantuan')

@section('content_header')
    <h1>Data bantuan</h1>
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

@push('js')
    <script src="{{ asset('assets/progressive-image/progressive-image.js') }}"></script>
@endpush

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
                        "filter[nama]": row.search.value,
                        "filter[asaldana]": row.search.value,
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
                    data: "attributes.nama", name: "nama"
                },
                {
                    data: "attributes.asaldana", name: "asaldana"
                },
                {
                    data: "attributes.jumlah_peserta", name: "jumlah_peserta", searchable: false, orderable: false
                },
                {
                    data: function (data) {
                        return data.attributes.sdate + ' - ' + data.attributes.edate
                    },
                    searchable: false, 
                    orderable: false
                },
                {
                    data: "attributes.nama_sasaran", name: "nama_sasaran", searchable: false, orderable: false
                },
                {
                    data: function (data) {
                        return data.attributes.status == 1 ? 'Aktif' : 'Tidak Aktif'
                    },
                    searchable: false,
                    orderable: false
                },
            ],
            order: [[ 1, 'asc' ]]
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
