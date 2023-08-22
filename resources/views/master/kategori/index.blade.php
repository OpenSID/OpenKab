@extends('layouts.index')

@section('title', 'Data Kategori Artikel')

@section('content_header')
<h1 id="subjudul">Data Kategori Artikel</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
<div class="row" id="tampilkan-bantuan">
    <div class="col-lg-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <div class="float-left">
                    <div class="btn-group">
                        @if (request()->route('parrent') != 0)
                        <a href="{{ url('master/kategori/0') }}" class="btn btn-sm btn-block btn-secondary"><i class="fas fa-arrow-left"></i>
                        </a>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <a class="btn btn-primary btn-sm" href="{{ url('master/kategori/tambah/') . '/' . request()->route('parrent') }}"><i class="far fa-plus-square"></i> Tambah</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="kategori">
                            <thead>
                                <tr>
                                    <th class="padat">No</th>
                                    <th class="padat">Aksi</th>
                                    <th>Kategori</th>
                                    <th>Jumlah artikel</th>
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
    <script nonce="{{ csp_nonce() }}"  type="text/javscript">
        $(function() {
            $.ajax({
                type: "get",
                url: "{{ url('api/v1/kategori/tampil') }}",
                data: {
                    id: {{ request()->route('parrent') }}
                },
                success: function(response) {
                    if (response.data != null) {
                        $('#subjudul').text(`Data sub kategori ${response.data.kategori}`)
                    } else {
                        $('#subjudul').text(`Data Kategori Artikel`)
                    }
                }
            });
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
                            "filter[parrent]": {{ request()->route('parrent') }},
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
                    [2, 'asc']
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
                        className: 'padat',
                    },
                    {
                        data: "attributes.id",
                        className: 'aksi',
                        render: function(data, type, row) {
                            var id = row.id;
                            var sub = (row.attributes.parrent == 0) ? `<a href="{{ url('master/kategori/') }}/${id}" class="btn btn-info btn-sm edit" data-id="${id}" title="Ubah"><i class="fas fa-bars"></i></a>` : '';
                            var render = `
                                    ${sub}
                                    <a href="{{ url('master/kategori/edit') }}/${id}/{{ (int) request()->route('parrent') }}" class="btn btn-warning btn-sm sub" data-id="${id}" title="Tambah Sub">
                                        <i class="fas fa-edit"></i>
                                    </a>
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
                    {
                        data: "attributes.jml_artikel",
                        className: 'w-56px text-center',
                        orderable: false,
                        name: "jml_artikel"
                    },
                ],
            });

            table.on('draw.dt', function() {
                var PageInfo = $('#kategori').DataTable().page.info();
                table.column(0, {
                    page: 'current'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + PageInfo.start;
                });
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
                            table.ajax.reload(null, false);
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
