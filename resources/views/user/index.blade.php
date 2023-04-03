@extends('layouts.index')

@section('title', 'Pengaturan Pengguna')

@section('content_header')
    <h1>Pengaturan Pengguna</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm" judul="Tambah Data"><i class="fa fa-plus"></i>&ensp;Tambah</a>
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
                                    <th>Surel</th>
                                    <th>Status</th>
                                    <th>Instansi</th>
                                    <th>Nomor HP</th>
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
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'aksi', name: 'action', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'username', name: 'username'},
                {data: 'email', name: 'email'},
                {data: 'active', name: 'active'},
                {data: 'company', name: 'company'},
                {data: 'phone', name: 'phone'},
            ]
        })
    </script>
@endsection
