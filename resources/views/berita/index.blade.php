@extends('layouts.index')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/progressive-image/progressive-image.css') }}">
@endpush

@section('title', 'Data Berita')

@section('content_header')
    <h1>Data Berita</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <form action="/berita" method="GET">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="id_kategori" placeholder="Kategori" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="config_id" placeholder="Kelurahan" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="bulan" placeholder="Bulan" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="tahun" placeholder="Tahun" class="form-control">
                            </div>
                        </div>
                        <div class="row mt-3 mb-3">
                            <div class="offset-10 col-md-2">
                                <input type="submit" value="CARI" class="form-control btn btn-primary">
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped" id="berita">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kelurahan</th>
                                    <th>Judul</th>
                                    <th>Tanggal Diupload</th>
                                    <th>Total Berita Perkelurahan</th>
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
        var berita = $('#berita').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            ajax: {
                url: @if(!empty($_GET)) `{{url('api/v1/berita?filter[id_kategori]='.$_GET['id_kategori'].'&filter[config_id]='.$_GET['config_id'].'&filter[bulan]='.$_GET['bulan'].'&filter[tahun]='.$_GET['tahun']) }}` @else `{{url('api/v1/berita')}}` @endif,
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
                    targets: '_all',
                    className: 'text-nowrap',
                },
                {
                    targets: [0, 1, 2, 3, 4],
                    orderable: false,
                    searchable: false,
                },
            ],
            columns: [{
                    data: null,
                    searchable: false,
                    orderable: false
                },
                {
                    data: function(data) {
                        return data.attributes.config?.nama_desa ?? null
                    },
                },
                {
                    data: "attributes.judul"
                },
                {
                    data: "attributes.tgl_upload"
                },{
                    data: function(data) {
                        return `{{ App\Models\Berita::count() }}`
                    },
                },
            ],
            order: [
                [1, 'asc']
            ]
        })

        berita.on('draw.dt', function() {
            var PageInfo = $('#berita').DataTable().page.info();
            berita.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });
    </script>
@endsection
