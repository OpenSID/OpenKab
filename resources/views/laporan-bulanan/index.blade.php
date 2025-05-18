@extends('layouts.index')

@section('title', $page_title ?? 'Page Title')

@section('content_header')
    <h1>{{ $page_description ?? '' }}</h1>
@stop

@section('content')
    <form id="mainform" name="mainform" action="{{ route('laporan-bulanan.index') }}" method="POST" class="form-horizontal">
            @csrf
        <div class="row">
            <div class="col-md-12">
                @if ($data_lengkap)
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <div class="float-left">{{ $page_description }}</div>
                            <div class="float-right">
                                <a href="{{ route('laporan-bulanan.export-excel') }}" class="btn btn-primary btn-sm" target="_blank">
                                    <i class="fa fa-print"></i>
                                    Cetak
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <h4 class="text-center"><strong>PEMERINTAH KABUPATEN/KOTA </strong></h4>
                            <h5 class="text-center"><strong>LAPORAN PERKEMBANGAN PENDUDUK (LAMPIRAN A - 9)</strong></h5>
                            <br />
                            <div class="form-group row">
                                <label for="kabupaten" class="col-sm-2 col-form-label">Kabupaten</label>
                                <div class="col-sm-7 col-md-5">
                                    <input type="text" class="form-control form-control-sm" value="{{ ucwords($identitasAplikasi['nama_kabupaten']) }}" disabled>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="tahun" class="col-sm-2 col-form-label">Tahun</label>
                                <div class="col-sm-2">
                                    <select class="form-control form-control-sm select2" onchange="formAction('mainform','{{ route('laporan-bulanan.bulan') }}')" name="tahun">
                                        <option value="">Pilih tahun</option>
                                        @for ($t = date('Y'); $t >= $tahun_lengkap; $t--)
                                            <option value="{{ $t }}" @selected($tahun == $t)>{{ $t }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <label for="bulan" class="col-sm-1 col-form-label text-right">Bulan</label>
                                <div class="col-sm-2">
                                    <select class="form-control form-control-sm select2" name="bulan" onchange="formAction('mainform','{{ route('laporan-bulanan.bulan') }}')">
                                        <option value="">Pilih bulan</option>
                                        @foreach (getAllBulan() as $no_bulan => $nama_bulan)
                                            <option value="{{ $no_bulan }}" @selected($bulan == $no_bulan)>{{ $nama_bulan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            @if ($sesudah_data_lengkap)
                                @include('laporan-bulanan.table_bulanan')
                            @else
                                <div class="box box-info">
                                    <div class="box-header with-border">
                                    </div>
                                    <div class="box-body">
                                        <div class="alert alert-warning">
                                            Tahun-bulan sebelum tanggal lengkap data pada {{ $tgl_lengkap }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    @include('laporan-bulanan.data_lengkap', ['judul' => 'Data Penduduk']);
                @endif
            </div>
        </div>
    </form>
@endsection

@section('js')
    <script type="text/javascript">
        function formAction(formId, actionUrl) {
            const form = document.getElementById(formId);
            if (form) {
                form.action = actionUrl;
                form.submit();
            } else {
                console.error('Form with ID ' + formId + ' not found');
            }
        }
    </script>
@endsection