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
                                <a href="{{ route('laporan-bulanan.export-excel') }}" class="btn btn-success btn-sm"
                                    target="_blank">
                                    <i class="fa fa-file-excel"></i>
                                    Excel
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
                                    <select name="kabupaten" class="form-control form-control-sm select2" id="kabupaten"
                                        onchange="formAction('mainform','{{ route('laporan-bulanan.filter') }}')">
                                        <option value="">
                                            {{ config('app.sebutanKab') }}</option>
                                        @foreach ($kabupatens as $item)
                                            <option value="{{ $item->kode_kabupaten }}"
                                                @if ($item->kode_kabupaten == session('kode_kabupaten')) selected @endif>
                                                {{ $item->nama_kabupaten }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="kecamatan" class="col-sm-2 col-form-label">Kecamatan</label>
                                <div class="col-sm-7 col-md-5">
                                    <select name="kecamatan" class="form-control form-control-sm select2" id="kecamatan"
                                        onchange="formAction('mainform','{{ route('laporan-bulanan.filter') }}')">
                                        <option value="">Pilih Kecamatan</option>
                                        @foreach ($kecamatans as $item)
                                            <option value="{{ $item->kode_kecamatan }}"
                                                @if ($item->kode_kecamatan == session('kode_kecamatan')) selected @endif>
                                                {{ $item->nama_kecamatan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="desa" class="col-sm-2 col-form-label">Desa</label>
                                <div class="col-sm-7 col-md-5">
                                    <select name="desa" class="form-control form-control-sm select2" id="desa"
                                        onchange="formAction('mainform','{{ route('laporan-bulanan.filter') }}')">
                                        <option value="">
                                            {{ config('app.sebutanDesa') }}</option>
                                        @foreach ($desas as $item)
                                            <option value="{{ $item->kode_desa }}"
                                                @if ($item->kode_desa == session('kode_desa')) selected @endif>{{ $item->nama_desa }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="tahun" class="col-sm-2 col-form-label">Tahun</label>
                                <div class="col-sm-2">
                                    <select class="form-control form-control-sm select2"
                                        onchange="formAction('mainform','{{ route('laporan-bulanan.filter') }}')"
                                        name="tahun">
                                        <option value="">Pilih tahun</option>
                                        @for ($t = date('Y'); $t >= $tahun_lengkap; $t--)
                                            <option value="{{ $t }}" @selected($tahun == $t)>
                                                {{ $t }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <label for="bulan" class="col-sm-1 col-form-label text-right">Bulan</label>
                                <div class="col-sm-2">
                                    <select class="form-control form-control-sm select2" name="bulan"
                                        onchange="formAction('mainform','{{ route('laporan-bulanan.filter') }}')">
                                        <option value="">Pilih bulan</option>
                                        @foreach (getAllBulan() as $no_bulan => $nama_bulan)
                                            <option value="{{ $no_bulan }}" @selected($bulan == $no_bulan)>
                                                {{ $nama_bulan }}</option>
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
    <script>
        document.addEventListener("DOMContentLoaded", function(event) {

            const header = @include('layouts.components.header_bearer_api_gabungan');
            var urlKecamatan = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/config/kecamatan' }}");

            $('#kabupaten').on('change', function() {
                let kodeKabupaten = $(this).val();
                $('#kecamatan').html('<option value="">Memuat...</option>');
                $('#desa').html(
                    '<option value="">{{ config('app.sebutanDesa') }}</option>'
                );

                url.searchParams.set("kode_kabupaten", kodeKabupaten);
                if (kodeKabupaten) {
                    $.get(urlKecamatan, {
                        headers: header
                    }, function(data) {
                        let options = '<option value="">Pilih Kecamatan</option>';
                        data.forEach(function(item) {
                            options +=
                                `<option value="${item.kode_kecamatan}">${item.nama_kecamatan}</option>`;
                        });
                        $('#kecamatan').html(options);
                    });
                } else {
                    $('#kecamatan').html('<option value="">Pilih Kecamatan</option>');
                }
            });

            $('#kecamatan').on('change', function() {
                let kodeKecamatan = $(this).val();
                $('#desa').html('<option value="">Memuat...</option>');

                if (kodeKecamatan) {
                    $.get('/api/desa/' + kodeKecamatan, function(data) {
                        let options =
                            '<option value="">{{ config('app.sebutanDesa') }}</option>';
                        data.forEach(function(item) {
                            options +=
                                `<option value="${item.kode_desa}">${item.nama_desa}</option>`;
                        });
                        $('#desa').html(options);
                    });
                } else {
                    $('#desa').html(
                        '<option value="">{{ config('app.sebutanDesa') }}</option>'
                    );
                }
            });
        });


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
