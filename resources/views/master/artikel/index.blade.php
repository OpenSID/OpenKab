@extends('layouts.index')

@section('title', 'Data Artikel')

@section('content_header')
    <h1 id="subjudul">Data Artikel</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row" id="tampilkan-artikel">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    @if ($canwrite)
                        <div class="row">
                            <div class="col-md-3">
                                <a class="btn btn-primary btn-sm" href="{{ route('master-data-artikel.create') }}"><i
                                        class="far fa-plus-square"></i> Tambah</a>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="artikel">
                            <thead>
                                <tr>
                                    <th class="padat">No</th>
                                    <th class="padat">Aksi</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Tanggal Upload</th>
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
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            const header = @include('layouts.components.header_bearer_api_gabungan');

            var table = $('#artikel').DataTable({
                ajax: {
                    url: `{{ config('app.databaseGabunganUrl') . '/api/v1/artikel/list' }}`,
                    headers: header,
                    dataSrc: function(json) {
                        json.recordsTotal = json.meta.pagination.total
                        json.recordsFiltered = json.meta.pagination.total
                        return json.data
                    },
                    data: function(row) {
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            "filter[search]": row.search.value,
                            "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]
                                ?.column]?.name
                        };
                    },
                },
                processing: true,
                stateSave: true,
                serverSide: true,
                ordering: true,
                order: [
                    [4, 'desc']
                ],
                columnDefs: [{
                        targets: '_all',
                        className: 'text-nowrap',
                    },
                    {
                        targets: [0, 1],
                        orderable: false,
                        searchable: false,
                    },
                ],
                columns: [{
                        data: null,
                        className: 'padat',
                    },
                    {
                        data: "attributes.id",
                        className: 'aksi',
                        render: function(data, type, row) {
                            let canEdit = `{{ $canedit }}`
                            let canDelete = `{{ $candelete }}`
                            var id = row.id;
                            let buttonEdit = canEdit ? `<a href="{{ route('master-data-artikel.index') }}/${id}/edit" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>` : ``;
                            let buttonDelete = canDelete ? `<button type="button" class="btn btn-danger btn-sm hapus" data-id="${id}" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>` : ``;
                            return `${buttonEdit} ${buttonDelete}`;
                        }
                    },
                    {
                        data: "attributes.judul",
                        className: 'judul',
                        orderable: true,
                        name: "judul"
                    },
                    {
                        data: "attributes.kategori_nama",
                        className: 'kategori',
                        orderable: false,
                        name: "kategori"
                    },
                    {
                        data: "attributes.tgl_upload",
                        className: 'tgl_upload',
                        orderable: true,
                        name: "tgl_upload"
                    },
                    {
                        data: "attributes.enabled",
                        className: 'text-center',
                        orderable: false,
                        render: function(data) {
                            return data == 1 ? '<span class="badge badge-success">Aktif</span>' :
                                '<span class="badge badge-secondary">Tidak Aktif</span>';
                        }
                    },
                ],
            });

            table.on('draw.dt', function() {
                var PageInfo = $('#artikel').DataTable().page.info();
                table.column(0, {
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
                    text: "Apakah anda yakin menghapus artikel ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus',
                            didOpen: () => {
                                Swal.showLoading()
                            },
                        })
                        $.ajax({
                            type: "DELETE",
                            headers: header,
                            dataType: "json",
                            url: "{{ config('app.databaseGabunganUrl') . '/api/v1/artikel/hapus/' }}" +
                                id,
                            success: function(response) {
                                if (response.success == true) {
                                    Swal.fire({
                                        title: 'Hapus!',
                                        text: 'Data berhasil dihapus',
                                        icon: 'success',
                                        showConfirmButton: true,
                                        timer: 1500
                                    })
                                    table.ajax.reload(null, false);
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: response.message,
                                        icon: 'error',
                                        timer: 1500,
                                        showConfirmButton: true,
                                    })
                                }
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                Swal.fire({
                                    title: 'Error!',
                                    text: xhr.responseJSON?.message ||
                                        thrownError,
                                    icon: 'error',
                                    timer: 1500,
                                    showConfirmButton: true,
                                })
                            }
                        });
                    }
                })
            });
        });
    </script>
@endsection
