@extends('layouts.index')

@section('title', 'Pengaturan Pengguna')

@section('content_header')
    <h1>Data Pengguna</h1>
@stop

@section('content')
    @include('partials.flash_message')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm" judul="Tambah Data"><i
                            class="fa fa-plus"></i>&ensp;Tambah</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="user">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Aksi</th>
                                    <th>Nama</th>
                                    <th>Nama Pengguna</th>
                                    <th>Group</th>
                                    <th>Surel</th>
                                    <th>Instansi</th>
                                    <th>Nomor HP</th>
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
    <script>
        var user = $('#user').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            ajax: "{{ route('users.list') }}",
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
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    className: 'text-center',
                    width: '1%'
                },
                {
                    data: 'aksi',
                    name: 'action',
                    width: '5%',
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'username',
                    name: 'username'
                },
                {
                    data: 'team[0].name',
                    name: 'team[0].name'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'active',
                    name: 'active',
                    className: 'text-center'
                },
            ],
            order: [
                [2, 'asc']
            ]
        })
    </script>
    @include('partials.delete_modal')
@endsection
