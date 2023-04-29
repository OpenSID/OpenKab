@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Program Bantuan')

@section('content_header')
    <h1>Data Program Bantuan</h1>
@stop

@section('content')
    <div class="row" id="tampilkan-bantuan">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-2">
                            <a href="{{ route('bantuan.create') }}">
                                <button type="button" class="btn btn-primary btn-sm"><i class="far fa-plus-square"></i>Tambah</button>
                            </a>
                        </div>

                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table " id="kategori">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Program</th>
                                    <th>Asal Dana</th>
                                    <th>Jumlah Peserta</th>
                                    <th>Masa Berlaku</th>
                                    <th>Sasaran</th>
                                    <th>Status</th>
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

            var table = $('#kategori').DataTable({
                dom: 't',
                ajax: {
                    url: '{{ url('api/v1/bantuan-kabupaten') }}',
                    dataSrc: 'data'
                },
                pageLength: 20,
                columns: [{
                        orderable: false,
                        data: null,
                        defaultContent: ''
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
                    {
                        data: "attributes.id",
                        className: 'aksi',
                        orderable: false,
                        render: function(data, type, row) {

                            var id = row.id;
                            var render = `
                                    <a href="{{ url('master/bantuan/${id}/edit') }}">
                                        <button type="button" class="btn btn-warning btn-sm edit" title="Ubah">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </a>

                                    <button type="button" class="btn btn-danger btn-sm hapus" data-id="${id}" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                   `;

                            return render;
                        }
                    },
                ],
                order: [
                    [1, 'asc']
                ]
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
                            url: "{{ url('api/v1/bantuan-kabupaten/hapus') }}",
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
