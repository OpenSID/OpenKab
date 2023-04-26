@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Peserta Bantuan')

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
                                    <th style="width: 80px">#</th>
                                    <th style="width: 80%">Kategori</th>
                                    <th style="width: 15%">Aksi</th>
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
            let nama_desa = `{{ session('desa.nama_desa') }}`;

            // Return table with id generated from row's name field
            function format(rowData) {
                var childTable = '<table id="cl' + rowData.id +
                    '" class="display compact nowrap w-100 table-striped"   style="margin:0px !important">' +
                    '<thead style="display:none"></thead >' +
                    '</table>';
                return $(childTable).toArray();
            }

            // Main table
            var table = $('#kategori').DataTable({
                dom: 't',
                ajax: {
                    url: '{{ url('api/v1/kategori') }}?filter[parrent]=0',
                    dataSrc: 'data'
                },
                pageLength: 20,
                columns: [{
                        className: 'details-control',
                        orderable: false,
                        data: null,
                        defaultContent: ''
                    },

                    {
                        data: "attributes.kategori",
                        className: 'kategori',
                    },

                    {
                        data: "attributes.id",
                        className: 'aksi',
                        orderable: false,
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
                ],
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
                        columns: [{
                                data: null,
                                defaultContent: '',
                                className: 'w-56px',
                            },
                            {
                                data: "attributes.kategori",
                                className: 'pl-5 w-80 kategori',
                            },
                            {
                                data: "attributes.id",
                                className: 'w-15',
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
                        ],
                        "columnDefs": [{
                            "width": "20%",
                            "targets": 0
                        }]
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
                            type: "POST",
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
                                    console.log(that.parent().parent().find(
                                        '.kategori'))
                                    that.parent().parent().find('.kategori').text(result
                                        .value)
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        response.message,
                                        'error'
                                    )
                                }

                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                console.log(thrownError);
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
                            type: "POST",
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
                console.log(parrent)
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
                    type: "PUT",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: "json",
                    url: "{{ url('api/v1/kategori/buat') }}",
                    data: {
                        kategori: value,
                        parrent: parrent
                    },
                    success: function(response) {
                        if (response.success == true) {
                            Swal.fire(
                                'Berhasil!',
                                'Data berhasil ditambahkan',
                                'success'
                            )
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
