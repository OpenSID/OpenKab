@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Daftar Terdata Suplemen')

@section('content_header')
    <h1>Daftar Terdata Suplemen</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @include('partials.flash_message')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-12">
                            @include('layouts.components.tombol_cetak_unduh', [
                                'cetak' => "suplemen/dialog_daftar/{$id}/cetak",
                                'unduh' => "suplemen/dialog_daftar/{$id}/unduh",
                            ])
                            @include('layouts.components.tombol_impor_ekspor', [
                                'impor' => "suplemen/impor_data/{$id}",
                                'ekspor' => "suplemen/ekspor/{$id}",
                            ])
                            <a href="#" title="Hapus Data" class="disabled btn btn-social btn-danger btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block hapus-terpilih"><i
                                    class='fa fa-trash-o'
                                ></i> Hapus</a>
                            <a href="{{ route('suplemen') }}" class="btn btn-social btn-info btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i class="fa fa-arrow-circle-left"></i> Kembali Ke Daftar Data Suplemen</a>
                        
                        </div>
                    </div>
                </div>
                <div class="card-header">
                    @include('suplemen.rincian')
                </div>
                <div class="card-body">
                    <div class="box-body">
                        <h5><b>Daftar Terdata</b></h5>
                        <div class="row mepet">
                            <div class="col-sm-2">
                                <select class="form-control input-sm" id="sex" name="sex">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="1">Laki-laki</option>
                                    <option value="2">Perempuan</option>
                                </select>
                            </div>
                            @include('layouts.components.wilayah')
                        </div>
                        <hr>
                        @include('suplemen.table_terdata')
                        
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script nonce="{{ csp_nonce() }}"  >
    document.addEventListener("DOMContentLoaded", function(event) {
        @include('suplemen.js.data_suplemen_terdata')
    })

    
    </script>
@endsection
