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
                    @include('riwayat_pengguna.filter')
                    <div class="table-responsive">
                        <table class="table table-striped" id="riwayat_pengguna">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Waktu</th>
                                    <!--
                                    <th>Riwayat</th>
                                    -->
                                    <th>Event</th>
                                    <th>Subjek Tipe</th>
                                    <th>Subjek Id</th>
                                    <th>Pelaku</th>
                                    <th>Data</th>
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
    <script src="{{ asset('assets/jsonview/jsonview.js') }}"></script>
@endpush
@section('css')
<style>
    .fa-caret-right:before {
        content: "";
    }
    .fa-caret-down:before {
        content: "";
    }
    .text-wrap{
        white-space:normal;
    }
    .width-600{
        width:600px;
    }
</style>
@endsection

@section('js')
    <script>
        let awalBulan = '{{ \Carbon\Carbon::now()->startOfMonth()->format('d-m-Y') }}'
        let akhirBulan = '{{ \Carbon\Carbon::now()->endOfMonth()->format('d-m-Y') }}'
        function getDateStr(elm){
            let obj = $(elm).datepicker('getDate')
            return [obj.getFullYear(), obj.getMonth() + 1, obj.getDate()].join('-')
        }
        var riwayat_pengguna = $('#riwayat_pengguna').DataTable({

            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            autoWidth: false,
            fixedColumns: true,
            ajax: {
                url: ``,
                method: 'get',
                data: function(row) {
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[created_at]": [getDateStr($('input[name=start]'))+' 00:00:00', getDateStr($('input[name=end]'))+' 23:59:59'],
                        "filter[causer_id]": $('select[name=causer_id]').val(),
                        "filter[search]": row.search.value,
                        "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]
                            ?.name,
                    };
                },
                dataSrc: function(json) {
                    json.recordsTotal = json.meta.pagination.total
                    json.recordsFiltered = json.meta.pagination.total

                    return json.data
                },
            },
            columns: [{
                    data: null,
                    searchable: false,
                    orderable: false
                },
                {
                    data: "attributes.created_at",
                    name: 'created_at'
                },
                // {
                //     data: "attributes.log_name",
                //     orderable: false
                // },
                {
                    data: "attributes.event",
                    orderable: false
                },
                {
                    data: "attributes.subject_type",
                    orderable: false
                },
                {
                    data: "attributes.subject_id",
                    orderable: false
                },
                {
                    data: "attributes.causer_name",
                    defaultContent: "",
                    orderable: false
                },
                {
                    data: "",
                    defaultContent: "<div class='tree text-wrap width-600'></div>",
                    orderable: false
                },
            ],
            rowCallback: function( row, data ) {
                const dataJson = Array.isArray(data.attributes.properties) ? {} : data.attributes.properties
                const tree = jsonview.create(dataJson);
                jsonview.render(tree, row.querySelector('.tree'));
                jsonview.expand(tree);
            },
            order: [
                [1, 'desc']
            ],
            columnDefs: [
                { "width": "150px", "targets": [6] },
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
            $('input[name=start]').datepicker('setDate',awalBulan);
            $('input[name=end]').datepicker('setDate',akhirBulan);
            $('select[name=causer_id]').val('').change();
            riwayat_pengguna.draw();
        });
    </script>
@endsection
