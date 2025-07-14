@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Program Bantuan')

@section('content_header')
    <h1>Data Program Bantuan</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row" id="tampilkan-bantuan">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    @if ($canwrite)
                        <div class="row">
                            <div class="col-md-2">
                                <a href="{{ route('bantuan.create') }}">
                                    <button type="button" class="btn btn-primary btn-sm"><i class="far fa-plus-square"></i>
                                        Tambah</button>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table " id="bantuan">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Aksi</th>
                                    <th>Nama Program</th>
                                    <th>Asal Dana</th>
                                    <th>Jumlah Peserta</th>
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
    <script nonce="{{ csp_nonce() }}">
        const header = @include('layouts.components.header_bearer_api_gabungan');
        var url = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/bantuan-kabupaten' }}");

        document.addEventListener("DOMContentLoaded", function(event) {
            let nama_desa = `{{ session('desa.nama_desa') }}`;
            var bantuan = $('#bantuan').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ordering: true,
                searchPanes: {
                    viewTotal: false,
                    columns: [0]
                },
                ajax: {
                    url: url.href,
                    method: 'get',
                    headers: header,
                    data: function(row) {
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            "filter[search]": row.search.value,
                            "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]
                                    ?.column]
                                ?.name,
                            "filter[sasaran]": $("#sasaran").val(),
                            "filter[tahun]": $("#tahun").val(),
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
                            let canEdit = `{{ $canedit }}`
                            let canDelete = `{{ $candelete }}`
                            let buttonEdit = canEdit ? `<a href="{{ url('master/bantuan/${data.id}/edit') }}">
                                    <button type="button" class="btn btn-warning btn-sm edit" title="Ubah">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </a>` : ``;
                            let buttonDelete = canDelete ? `<button type="button" class="btn btn-danger btn-sm hapus" data-id="${data.id}" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>` : ``;
                            return `${buttonEdit} ${buttonDelete}`;
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

                        var url = new URL(
                            "{{ config('app.databaseGabunganUrl') . '/api/v1/bantuan-kabupaten/hapus' }}"
                            );

                        $.ajax({
                            type: "POST",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                    'content'),
                                'Authorization': 'Bearer {{ $settingAplikasi->get('database_gabungan_api_key') }}'
                            },
                            dataType: "json",
                            url: url,
                            data: {
                                id: id
                            },
                            success: function(response) {

                                if (response.success == true) {
                                    Swal.fire(
                                        'Hapus!',
                                        'Data berhasil dihapus',
                                        'success'
                                    )
                                    that.parent().parent().remove();
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        response.message,
                                        'error'
                                    )
                                }
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                Swal.fire(
                                    'Error!',
                                    thrownError,
                                    'error'
                                )

                            }
                        });
                    }
                })
            });
        });
    </script>
@endsection
