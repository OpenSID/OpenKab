@extends('layouts.index')

@section('title', 'Data Kategori Artikel')

@section('content_header')
    <h1>Data Kategori Artikel</h1>
@stop

@section('content')
    <div class="row" id="tampilkan-bantuan">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-2">
                            <button id="tambah" type="button" class="btn btn-primary btn-block btn-sm" data-url=""><i
                                    class="far fa-plus-square"></i>
                                Tambah</button>
                        </div>

                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table " id="kategori">
                            <thead>
                                <tr>
                                    <th class="padat">#</th>
                                    <th class="padat">No</th>
                                    <th class="padat">Aksi</th>
                                    <th>Kategori</th>
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
        $(function() {
            function format(rowData) {
                var childTable = '<table id="cl' + rowData.id +
                    '" class="display compact nowrap w-100 table-striped" style="margin:0px !important">' +
                    '<thead style="display:none"></thead >' +
                    '</table>';
                return $(childTable).toArray();
            }

            var table = $('#kategori').DataTable({

                ajax: {
                    url: '{{ url('api/v1/kategori') }}',
                    dataSrc: function(json) {
                        json.recordsTotal = json.meta.pagination.total
                        json.recordsFiltered = json.meta.pagination.total
                        return json.data
                    },
                    data: function(row) {
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            "filter[parrent]": 0,
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
                    [4, 'asc']
                ],
                columnDefs: [{
                        targets: '_all',
                        className: 'text-nowrap',
                    },
                    {
                        targets: [0, 1, 2],
                        orderable: false,
                        searchable: false,
                    },
                ],
                columns: [{
                        data: null,
                        className: 'details-control padat',
                        defaultContent: ''
                    },
                    {
                        data: null,
                        className: 'padat',
                    },
                    {
                        data: "attributes.id",
                        className: 'aksi',
                        render: function(data, type, row) {
                            var id = row.id;
                            var render = `
                                    <button type="button" class="btn btn-info btn-sm sub" data-id="${id}" title="Tambah Sub">
                                        <i class="far fa-plus-square"></i>
                                    </button>

                                    <button type="button" class="btn btn-warning btn-sm edit" data-id="${id}" title="Ubah">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button" class="btn btn-danger btn-sm hapus" data-id="${id}" title="Ubah">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                `;

                            return render;
                        }
                    },
                    {
                        data: "attributes.kategori",
                        className: 'kategori',
                        orderable: true,
                        name: "kategori"
                    },
                ],
            });

            table.on('draw.dt', function() {
                var PageInfo = $('#kategori').DataTable().page.info();
                table.column(1, {
                    page: 'current'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + PageInfo.start;
                });
            });

            // Add event listener for opening and closing first level childdetails
            $('#kategori tbody').on('click', 'td.details-control', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var rowData = row.data();

                if (row.child.isShown()) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');

                    // Destroy the Child Datatable
                    $('#cl' + rowData.id).DataTable().destroy();
                } else {

                    row.child(format(rowData)).show();
                    var id = rowData.id;

                    $('#cl' + id).parent().addClass('p-0')
                    childTable = $('#cl' + id).DataTable({
                        dom: "t",
                        ajax: {
                            url: '{{ url('api/v1/kategori') }}?filter[parrent]=' + rowData.id,
                            dataSrc: 'data'
                        },
                        columnDefs: [{
                                targets: '_all',
                                className: 'text-nowrap',
                            },
                            {
                                targets: [0, 1, 2],
                                orderable: false,
                                searchable: false,
                            },
                        ],
                        columns: [{
                                data: null,
                                className: 'w-70px',
                                defaultContent: '',
                            },
                            {
                                data: "attributes.id",
                                className: 'aksi w-100px',
                                "render": function(data, type, row) {
                                    data = `
                                    <button type="button" class="btn btn-warning btn-sm edit" data-id="${row.id}" title="ubah">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button type="button" class="btn btn-danger btn-sm hapus" data-id="${row.id}" title="ubah">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    `;

                                    return data;
                                }
                            },
                            {
                                data: "attributes.kategori",
                                className: 'kategori',
                            },
                        ],
                    });

                    tr.addClass('shown');
                }
            });

            $(document).on('click', 'button.edit', function() {
                var id = $(this).data('id')
                var that = $(this)
                Swal.fire({
                    title: 'Ubah kategori',
                    input: 'text',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Simpan',

                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menyimpan',
                            didOpen: () => {
                                Swal.showLoading()
                            },
                        })

                        $.ajax({
                            type: "PUT",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            // contentType: "application/json; charset=utf-8",
                            dataType: "json",
                            url: "{{ url('api/v1/kategori/perbarui') }}/" + id,
                            data: {
                                kategori: result.value
                            },
                            success: function(response) {

                                if (response.success == true) {
                                    Swal.fire(
                                        'Perbarui!',
                                        'Data berhasil diperbarui',
                                        'success'
                                    )
                                    that.parent().parent().find('.kategori').text(result
                                        .value)
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
                                    text: thrownError,
                                    icon: 'error',
                                    timer: 1500,
                                    showConfirmButton: true,
                                })
                            }
                        });

                    }
                })
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
                        $.ajax({
                            type: "post",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType: "json",
                            url: "{{ url('api/v1/kategori/hapus') }}",
                            data: {
                                id: id
                            },
                            success: function(response) {

                                if (response.success == true) {
                                    Swal.fire({
                                        title: 'Hapus!',
                                        text: 'Data berhasil dihapus',
                                        icon: 'success',
                                        showConfirmButton: true,
                                        timer: 1500
                                    })
                                    that.parent().parent().remove();
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
                                    text: thrownError,
                                    icon: 'error',
                                    timer: 1500,
                                    showConfirmButton: true,
                                })
                            }
                        });
                    }
                })
            });

            $(document).on('click', 'button#tambah', function() {
                Swal.fire({
                    title: 'Tambah kategori',
                    input: 'text',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Simpan',
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menyimpan',
                            didOpen: () => {
                                Swal.showLoading()
                            },
                        });
                        simpan(result.value);
                    }
                });
            });

            $(document).on('click', 'button.sub', function() {
                let parrent = $(this).data('id')
                Swal.fire({
                    title: 'Tambah Sub Kategori',
                    input: 'text',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Simpan',
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menyimpan',
                            didOpen: () => {
                                Swal.showLoading()
                            },
                        });
                        simpan(result.value, parrent);
                    }
                });
            });

            function simpan(value, parrent = 0) {
                $.ajax({
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: "json",
                    url: `{{ url('api/v1/kategori/buat') }}`,
                    data: {
                        kategori: value,
                        parrent: parrent
                    },
                    success: function(response) {
                        if (response.success == true) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Data berhasil ditambahkan',
                                icon: 'success',
                                showConfirmButton: true,
                                timer: 1500
                            })
                            location.reload();
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
        });
    </script>
@endsection
