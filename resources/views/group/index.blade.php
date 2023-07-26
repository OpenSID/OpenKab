@extends('layouts.index')

@section('title', $nama_aplikasi)

@section('content_header')
    <h1>{{ $nama_aplikasi }}</h1>
@stop

@section('content')

    <div class="row" x-data="group()" x-init="retrieveData()">

        <div class="col-sm-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a x-bind:href="'{{ url('pengaturan/groups/tambah') }}'">
                        <button type="button" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah </button>
                    </a>
                </div>

                <div class="card-body">

                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="user">
                                <thead>
                                    <tr>
                                        <th width="50">No</th>
                                        <th width="150">Aksi</th>
                                        <th>Group</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(value, index) in dataGroups">
                                        <tr>
                                            <td class="text-center"><span x-text="index+1"></span></td>
                                            <td class="text-center">
                                                <template x-if="value.attributes.name != 'administrator'">
                                                    <a x-bind:href="'{{ url('pengaturan/groups/edit') }}/' + value.id">
                                                        <button type="button" title="edit" class="btn btn-info btn-sm btn-flat"><i class="fas fa-edit"></i></button>
                                                    </a>
                                                </template>

                                                <template x-if="value.attributes.name != 'administrator'">
                                                    <button type="button" title="hapus" class="btn btn-danger btn-sm btn-flat" x-on:click="hapus(value.id)"> <i class="fas fa-trash-alt"></i></button>
                                                </template>

                                            </td>
                                            <td x-text="value.attributes.name"></td>
                                        </tr>
                                    </template>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
    <script>
        function group() {
            return {
                id: 1,

                dataGroups: {},
                retrieveData() {
                    fetch('{{ url('api/v1/pengaturan/group/') }}')
                        .then(res => res.json())
                        .then(response => {
                            this.dataGroups = response.data;
                        });
                },

                hapus(id) {

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
                    return;

                }
            }
        }
    </script>
@endsection
