@extends('layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Pengaturan Pengguna</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">pengaturan Pengguna</li>
                </ol>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</section>

@include('partials.flash_message')

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm" judul="Tambah Data"><i class="fa fa-plus"></i>&ensp;Tambah</a>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table class="table table-bordered yajra-datatable">
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
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection

@include('partials.asset_datatable')

@push('page_scripts')
<script type="module">
    $(function () {
    
    var table = $('.yajra-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('users.list') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'aksi', name: 'action', orderable: false, searchable: false},
            {data: 'nama', name: 'nama'},
            {data: 'username', name: 'username'},
            {data: 'email', name: 'email'},
            {data: 'active', name: 'active'},
            {data: 'company', name: 'company'},
            {data: 'phone', name: 'phone'},
        ]
    });
    
  });
</script>
@endpush
