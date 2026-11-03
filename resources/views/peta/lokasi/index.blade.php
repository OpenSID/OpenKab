@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Lokasi')

@section('content_header')
    <h1>Data Lokasi</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @include('partials.flash_message')

    @if (Session::has('import_summary'))
        @include('peta.lokasi.import_summary')
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <button type="button" class="btn btn-sm btn-secondary" data-toggle="collapse"
                        data-target="#collapse-filter" data-testid="bt-toggle-filter">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
                <div class="card-body">
                    @include('peta.lokasi.filter')
                    <div class="row" style="margin-bottom: 10px;">
                        <div class="col-sm-12">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                data-target="#modal-import" data-testid="bt-import">
                                <i class="fas fa-file-import"></i> Import
                            </button>
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal"
                                data-target="#modal-form" data-testid="bt-tambah">
                                <i class="far fa-plus-square"></i> Tambah
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" id="bt-bulk-delete"
                                data-testid="bt-bulk-delete">
                                <i class="fas fa-trash"></i> Hapus Terpilih
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        @include('peta.lokasi.table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('peta.lokasi.import')
    @include('peta.lokasi.form')
@endsection

@include('components.wilayah_filter_js')

@push('css')
    <style nonce="{{ csp_nonce() }}">
        .select2-results__option[aria-disabled=true] {
            display: none;
        }
    </style>
@endpush

@section('js')
    <script nonce="{{ csp_nonce() }}">
        window.lokasiConfig = @js([
            'desa' => $desa ?? [],
            'desaList' => $desaList ?? [],
            'jenisKategori' => $jenisKategori ?? [],
            'urls' => [
                'template' => route('plan.import.template'),
                'import' => route('plan.import'),
                'store' => route('plan.store'),
                'update' => url('plan/update'),
                'destroy' => url('plan/hapus'),
                'bulkDestroy' => route('plan.destroy.bulk'),
                'detail' => url('plan/detail'),
            ],
            'csrf' => csrf_token(),
        ]);
    </script>
    <script nonce="{{ csp_nonce() }}">
        @include('peta.lokasi.js.app')
    </script>
@endsection
