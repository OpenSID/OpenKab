@extends('layouts.index')

@section('title', $config ? 'Ubah Konfigurasi SSO Desa' : 'Tambah Konfigurasi SSO Desa')

@section('content_header')
    <h1>{{ $config ? 'Ubah Konfigurasi SSO Desa' : 'Tambah Konfigurasi SSO Desa' }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-12">
            @include('common.errors')
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ route('sso-config.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-circle-left"></i>&ensp;Kembali ke Daftar
                    </a>
                </div>
                {!! Html::form($config ? 'PUT' : 'POST', $config ? route('sso-config.update', $config) : route('sso-config.store'))
                    ->class('form-horizontal') !!}

                <div class="card-body">
                    <div class="form-group">
                        <label for="desa_id">Kode Desa <span class="text-danger">*</span></label>
                        {!! Html::text('desa_id')->id('desa_id')->class('form-control')->placeholder('contoh: 5271010001')
                            ->value(old('desa_id', $config?->desa_id)) !!}
                        <small class="form-text text-muted">Kode desa (10-13 digit) sesuai data wilayah.</small>
                    </div>
                    <div class="form-group">
                        <label for="opensid_url">URL OpenSID <span class="text-danger">*</span></label>
                        {!! Html::text('opensid_url')->id('opensid_url')->class('form-control')
                            ->placeholder('https://desa.contoh.id')
                            ->value(old('opensid_url', $config?->opensid_url)) !!}
                        <small class="form-text text-muted">Base URL instalasi OpenSID desa (tanpa trailing slash).</small>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-inline">
                            {!! Html::checkbox('enabled', 1, old('enabled', $config?->enabled ?? true)) !!}
                            Aktifkan konfigurasi ini
                        </label>
                    </div>
                </div>

                <div class="card-footer">
                    {!! Html::button('<i class="fas fa-times"></i> Batal')->type('reset')->class('btn btn-danger btn-sm') !!}
                    {!! Html::button('<i class="fas fa-save"></i> Simpan')->type('submit')->class('btn btn-primary btn-sm') !!}
                </div>

                {!! Html::form()->close() !!}
            </div>
        </div>
    </div>
@endsection
