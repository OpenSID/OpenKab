@extends('layouts.index')

@section('title', $nama_aplikasi)

@section('content_header')
    <h1>{{ $nama_aplikasi }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')

    <div class="row">

        <div class="col-sm-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ url('pengaturan/groups/tambah') }}">
                        <button type="button" class="btn btn-primary btn-sm"><i class="far fa-plus-square"></i> Tambah</button>
                    </a>
                </div>

                <div class="card-body">

                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="grup">
                                <thead>
                                    <tr>
                                        <th width="50">No</th>
                                        <th width="150">Aksi</th>
                                        <th>Grup</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
@endsection
@section('js')
    <script nonce="{{ csp_nonce() }}"  type="text/javscript">
        $(function() {
            var grup = $('#grup').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            ajax: {
                url: `{{ url('api/v1/pengaturan/group') }}`,
                method: 'get',
                data: function(row) {
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[name]": row.search.value
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
                },
                {
                    data: function(data) {
                        if (data.attributes.name == 'administrator') {
                            return ``;
                        }
                        return `
                                <a href="{{ url('pengaturan/groups/edit/${data.id}') }}">
                                    <button type="button" class="btn btn-warning btn-sm edit" title="Ubah">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </a>

                                <button type="button" class="btn btn-danger btn-sm hapus" data-id="${data.id}" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                                `;
                    },
                },
                {
                    data: "attributes.name",
                    name: "nama"
                },
            ],
            order: [
                [2, 'asc']
            ]
        })

        grup.on('draw.dt', function() {
            var PageInfo = $('#grup').DataTable().page.info();
            grup.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });


        $(document).on('click', 'button.hapus', function() {
                var id = $(this).data('id')
                var that = $(this);
                Swal.fire({
                        title: 'Hapus group',
                        text: "Data yang dihapus tidak bisa dikembalikan. Yakin untuk menghapus?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Hapus',
                        cancelButtonText: 'Batal'
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
                                url: '{{ url('api/v1/pengaturan/group/delete') }}',
                                data: {
                                    id: id
                                },
                                dataType: "json",
                                success: function(response) {
                                    if (response.success == true) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Data berhasil dihapus',
                                            showConfirmButton: false,
                                            timer: 1500
                                        })
                                        window.location.replace("{{ url('pengaturan/groups') }}");
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
                                        'Error!  ' + xhr.status,
                                        JSON.parse(xhr.responseText).message,
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
