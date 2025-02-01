@extends('layouts.index')

@section('content_header')
    <h1>Pengaturan OpenDK</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                @include('adminlte-templates::common.alerts')

                @include('opendk.setting')

                <div class="card card-outline card-primary">
                    <div class="card-body">
                        @include('opendk.table')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script nonce="{{ csp_nonce() }}">
            document.addEventListener("DOMContentLoaded", function(event) {
                let settings = $('#settings-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ordering: true,
                searchPanes: {
                    viewTotal: false,
                    columns: [0]
                },
                ajax: {
                    url: `{{ route('synchronize.opendk.index') }}`,
                    method: 'get',
                    data: function(row) {
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
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
                columnDefs: [{
                        targets: '_all',
                        className: 'text-nowrap',
                    },
                ],
                columns: [{
                        data: null,
                    },
                    {
                        data: "attributes.kode_kecamatan",
                        name: "kode_kecamatan"
                    },
                    {
                        data: "attributes.nama_kecamatan",
                        name: "nama_kecamatan"
                    },
                    {
                        data: "attributes.updated_at",
                        name: "updated_at"
                    },                    
                ],
                order: [
                    [0, 'asc']
                ]
            })

            settings.on('draw.dt', function() {
                var PageInfo = $('#settings-table').DataTable().page.info();
                settings.column(0, {
                    page: 'current'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + PageInfo.start;
                });
            });                
        });

    </script>
@endsection
