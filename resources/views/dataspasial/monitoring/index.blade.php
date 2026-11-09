@extends('layouts.index')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @include('partials.flash_message')

    {{-- Blok Filter (komponen pakai-ulang sektor + kecamatan — FR-005) --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-filter"></i> Filter</h3></div>
                <div class="card-body">
                     <x-dataspasial-filter-konteks
                        :sektor-list="$sektorList"
                        :data-url="route('dataspasial.monitoring.data')"
                        :ekspor-url="route('dataspasial.monitoring.ekspor')"
                    />
                </div>
            </div>
        </div>
    </div>

    {{-- Kartu ringkasan --}}
    <div class="row" id="monitoring-kartu" data-testid="monitoring-kartu" style="display:none">
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card card-outline card-success h-100 mb-0">
                <div class="card-body py-2">
                    <div class="text-muted small">Desa sudah mengisi</div>
                    <h3 class="mb-0" data-testid="kartu-sudah-label">0/0</h3>
                    <small class="text-muted d-none" data-testid="kartu-sudah"></small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card card-outline card-danger h-100 mb-0">
                <div class="card-body py-2">
                    <div class="text-muted small">Belum pernah mengirim</div>
                    <h3 class="mb-0" data-testid="kartu-belum">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card card-outline card-warning h-100 mb-0">
                <div class="card-body py-2">
                    <div class="text-muted small">Perlu diperiksa</div>
                    <h3 class="mb-0" data-testid="kartu-periksa">0</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Pesan keadaan --}}
    <div class="row">
        <div class="col-12">
            <div id="monitoring-loading" class="text-muted" style="display:none" data-testid="monitoring-loading">
                <i class="fas fa-spinner fa-spin"></i> Memuat data monitoring…
            </div>
            <div id="monitoring-validation" class="alert alert-info" style="display:none" data-testid="monitoring-validation"></div>
            <div id="monitoring-empty" class="alert alert-secondary" style="display:none" data-testid="monitoring-empty">
                Tidak ada data untuk pilihan ini.
            </div>
            <div id="monitoring-unavailable" class="alert alert-warning" style="display:none" data-testid="monitoring-unavailable">
                <h5 class="alert-heading">Data sementara tidak tersedia</h5>
                <p class="mb-0">Silakan coba lagi nanti.</p>
            </div>
        </div>
    </div>

    {{-- Tabel kelengkapan data per desa --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-table"></i> Kelengkapan Data Desa</h3>
                    <span class="badge badge-info" id="monitoring-meta-periode"></span>
                    <div class="card-tools">
                        <button type="button" id="bt-ekspor-csv" class="btn btn-sm btn-primary"
                            data-testid="bt-ekspor-csv" title="Ekspor tabel kelengkapan ini ke CSV">
                            <span class="fas fa-file-csv"></span> Ekspor CSV
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover mb-0" data-testid="tabel-monitoring">
                            <thead class="thead-light">
                                <tr>
                                    <th>Desa</th>
                                    <th>Kecamatan</th>
                                    <th style="min-width:140px">Kelengkapan</th>
                                    <th>Kualitas</th>
                                    <th>Status</th>                                    
                                </tr>
                            </thead>
                            <tbody id="monitoring-tbody" data-testid="monitoring-tbody">
                                <tr class="table-loading"><td colspan="5" class="text-center text-muted">Pilih sektor terlebih dahulu.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <nav id="monitoring-paginasi" data-testid="monitoring-paginasi"></nav>
                </div>
            </div>
        </div>
    </div>

    </div>
@stop

@push('js')
    <script src="{{ asset('js/dataspasial/monitoring.js') }}"></script>
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('DOMContentLoaded', function () {
            if (window.MonitoringDataSpasial) {
                window.MonitoringDataSpasial.init(window.MonitoringFilterKomponen || {});
            }
        });
    </script>
@endpush
