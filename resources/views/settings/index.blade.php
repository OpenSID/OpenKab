@extends('layouts.index')

@section('content_header')
    <h1>Data Setting</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                @include('common.alerts')

                @include('settings.opensid')

                <div class="card card-outline card-primary">
                    <div class="card-body">
                        @include('settings.table')
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
                    url: `{{ route('settings.index') }}`,
                    method: 'get',
                    data: function(row) {
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            "filter[search]": row.search.value,
                            "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]
                                    ?.column]
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
                }, ],
                columns: [{
                        data: null,
                    },
                    {
                        data: "attributes.key",
                        name: "key"
                    },
                    {
                        data: "attributes.name",
                        name: "name"
                    },
                    {
                        data: "attributes.value",
                        name: "value"
                    },
                    {
                        data: "attributes.description",
                        name: "description"
                    },
                    {
                        data: function(data) {
                            let canEdit = `{{ $canedit }}`
                            let buttonEdit = canEdit ? `<a href="{{ route('settings.index') }}/${data.id}/edit">
                                        <button type="button" class="btn btn-warning btn-sm edit" title="Ubah">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </a>` : ``;
                            return `${buttonEdit}`;
                        },
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

            $(document).on('click', 'button.hapus', function() {
                var id = $(this).data('id')
                var that = $(this);
                Swal.fire({
                    title: 'Hapus',
                    text: "Apakah anda yakin menghapus data ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menyimpan',
                            didOpen: () => {
                                Swal.showLoading()
                            },
                        })
                    }
                })
            });
        });
    </script>
@endsection
