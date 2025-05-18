@extends('layouts.index')

@section('title', $page_title ?? 'Page Title')

@section('content_header')
    <h1>{{ $page_description ?? '' }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary">
            <form id="mainform" name="mainform" method="post">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-12">
                            <a href="{{ route('laporan-bulanan.export-excel') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-print"></i>
                                Cetak
                            </a>
                            <a href="{{ route('laporan-bulanan.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                Kembali Ke Laporan Bulanan
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="text-center"><strong>RINCIAN LAPORAN PERKEMBANGAN {{ $title }}</strong></h5>
                    </br>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>NIK</th>
                                    <th>Tempat Lahir</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Nama Ayah</th>
                                    <th>Nama Ibu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($main as $key => $data)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $data['nama'] }}</td>
                                        <td>{{ $data['nik'] }}</td>
                                        <td>{{ $data['tempatlahir'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($data['tanggallahir'])->format('Y-m-d') }}</td>
                                        <td>{{ $data['nama_ayah'] }}</td>
                                        <td>{{ $data['nama_ibu'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>
@endsection