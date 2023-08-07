@extends('layouts.index')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/progressive-image/progressive-image.css') }}">
@endpush

@section('title', 'Data riwayat_pengguna')

@section('content_header')
    <h1>Data Riwayat Pengguna</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-3">
                            <a class="btn btn-sm btn-secondary" data-toggle="collapse" href="#collapse-filter" role="button"
                                aria-expanded="false" aria-controls="collapse-filter">
                                <i class="fas fa-filter"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="collapse-filter" class="collapse">
                                <div class="row">
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>created_at</label>

                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="btn-group btn-group-sm btn-block">
                                                    <button type="button" id="reset" class="btn btn-secondary"><span
                                                            class="fas fa-ban"></span></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="btn-group btn-group-sm btn-block">
                                                    <button type="button" id="filter" class="btn btn-primary"><span
                                                            class="fas fa-search"></span></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr class="mt-0">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="riwayat_pengguna">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Riwayat</th>
                                    <th>Event</th>
                                    <th>Keterangan</th>
                                    <th>Subjek</th>
                                    <th>Data</th>
                                    <th>Even</th>
                                    <th>Pengguna</th>
                                    <th>Waktu</th>
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
@include('partials.asset_datepicker')
@push('js')
    <script src="{{ asset('assets/progressive-image/progressive-image.js') }}"></script>
@endpush

@section('js')
    <script>
        var riwayat_pengguna = $('#riwayat_pengguna').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            ajax: {
                url: ``,
                method: 'get',
                data: function(row) {
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[created_at]": $('#created_at').val(),
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
                    targets: [0, 5],
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
                    data: "attributes.log_name"
                },
                {
                    data: "attributes.event"
                },
                {
                    data: "attributes.subject"
                },
                {
                    data: "attributes.causer"
                },
                {
                    data: "attributes.properties",
                    width: "100px"
                },
                {
                    data: "attributes.created_at"
                }
            ],
            order: [
                [5, 'asc']
            ]
        })

        riwayat_pengguna.on('draw.dt', function() {
            var PageInfo = $('#riwayat_pengguna').DataTable().page.info();
            riwayat_pengguna.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });

        $('#filter').on('click', function(e) {
            riwayat_pengguna.draw();
        });

        $(document).on('click', '#reset', function(e) {
            e.preventDefault();
            $('#created_at').val('').change();

            riwayat_pengguna.ajax.reload();
        });
    </script>
@endsection
