{{--
    Halaman Log Entri Audit DataSpasial (KAB-08 / API-08).

    Isi: filter (sumber, desa, sektor, aksi, pelaku, rentang tanggal),
    kartu ringkasan entri per sumber, tabel log dengan badge sumber, dan
    detail baris (perbandingan sebelum/sesudah per isian).
--}}
@extends('layouts.index')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @include('partials.flash_message')

    {{-- Blok Filter --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-filter"></i> Filter</h3></div>
                <div class="card-body">
                    <form id="audit-filter" data-testid="audit-filter" onsubmit="return false;">
                        <div class="row">
                            <div class="col-lg col-md-4 col-sm-6 mb-2">
                                <label for="filter_sumber">Sumber</label>
                                <select id="filter_sumber" class="form-control form-control-sm" data-testid="filter-sumber"
                                    title="Pilih Sumber">
                                    <option value="">Semua Sumber</option>
                                    <option value="openkab">OpenKab</option>
                                    <option value="opensid">OpenSID</option>
                                </select>
                            </div>
                            <div class="col-lg col-md-4 col-sm-6 mb-2">
                                <x-dataspasial-dropdown-kecamatan id="filter_kecamatan_audit" name="kode_kecamatan"
                                    data-testid="filter-kecamatan" />
                            </div>
                            <div class="col-lg col-md-4 col-sm-6 mb-2">
                                <x-dataspasial-dropdown-desa :desa-list="$desaList" id="filter_desa_audit" name="kode_desa"
                                    data-testid="filter-desa" />
                            </div>
                            <div class="col-lg col-md-4 col-sm-6 mb-2">
                                <x-dataspasial-dropdown-sektor :sektor-list="$sektorList" id="filter_sektor_audit"
                                    name="sektor" :required="false" data-testid="filter-sektor" />
                            </div>
                            <div class="col-lg col-md-4 col-sm-6 mb-2">
                                <label for="filter_aksi">Aksi</label>
                                <select id="filter_aksi" class="form-control form-control-sm" data-testid="filter-aksi"
                                    title="Pilih Aksi">
                                    <option value="">Semua Aksi</option>
                                    <option value="create">Tambah</option>
                                    <option value="update">Ubah</option>
                                    <option value="delete">Hapus</option>
                                    <option value="import">Impor</option>
                                    <option value="publish">Terbitkan</option>
                                </select>
                            </div>
                            <div class="col-lg col-md-4 col-sm-6 mb-2">
                                <label for="filter_pelaku">Pelaku</label>
                                <input type="text" id="filter_pelaku" class="form-control form-control-sm"
                                    data-testid="filter-pelaku" maxlength="255" placeholder="Nama pelaku">
                            </div>
                            <div class="col-lg col-md-4 col-sm-6 mb-2">
                                <label for="filter_waktu_dari">Waktu Dari</label>
                                <input type="date" id="filter_waktu_dari" class="form-control form-control-sm"
                                    data-testid="filter-waktu-dari">
                            </div>
                            <div class="col-lg col-md-4 col-sm-6 mb-2">
                                <label for="filter_waktu_sampai">Waktu Sampai</label>
                                <input type="date" id="filter_waktu_sampai" class="form-control form-control-sm"
                                    data-testid="filter-waktu-sampai">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-12 mb-2">
                                <button type="button" id="bt-reset-audit" class="btn btn-secondary btn-block"
                                    data-testid="bt-reset-audit">
                                    <span class="fas fa-ban"></span> Reset
                                </button>
                            </div>
                            <div class="col-md-6 col-12 mb-2">
                                <button type="button" id="bt-filter-audit" class="btn btn-primary btn-block"
                                    data-testid="bt-filter-audit">
                                    <span class="fas fa-search"></span> Tampilkan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Kartu ringkasan per sumber --}}
        <div class="col-lg-3 col-md-12 mb-3">
            <div class="card card-outline card-primary h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie"></i> Ringkasan Entri</h3>
                    <span class="badge badge-info" id="audit-meta-periode" data-testid="audit-meta-periode"></span>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">OpenKab</span>
                        <span class="badge badge-primary" id="audit-kartu-openkab" data-testid="kartu-openkab">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">OpenSID</span>
                        <span class="badge badge-info" id="audit-kartu-opensid" data-testid="kartu-opensid">0</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Total entri</span>
                        <strong id="audit-kartu-total" data-testid="kartu-total">0</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel log --}}
        <div class="col-lg-9 col-md-12 mb-3">
            <div class="card card-outline card-primary h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-table"></i> Log Entri</h3>
                    <div class="card-tools">
                        <button type="button" id="bt-ekspor-audit" class="btn btn-sm btn-primary"
                            data-testid="bt-ekspor-audit" title="Ekspor hasil filter ke Excel">
                            <span class="fas fa-file-excel"></span> Ekspor Excel
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="audit-loading" class="text-muted p-3" style="display:none" data-testid="audit-loading">
                        <i class="fas fa-spinner fa-spin"></i> Memuat log entri…
                    </div>
                    <div id="audit-validation" class="alert alert-info m-3" style="display:none"
                        data-testid="audit-validation"></div>
                    <div id="audit-empty" class="alert alert-secondary m-3" style="display:none" data-testid="audit-empty">
                        Tidak ada data untuk pilihan ini.
                    </div>
                    <div id="audit-unavailable" class="alert alert-warning m-3" style="display:none"
                        data-testid="audit-unavailable">
                        <h5 class="alert-heading">Data sementara tidak tersedia</h5>
                        <p class="mb-0">Silakan coba lagi nanti.</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover mb-0" data-testid="tabel-audit">
                            <thead class="thead-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Pelaku</th>
                                    <th>Sumber</th>
                                    <th>Aksi</th>
                                    <th>Sasaran</th>
                                    <th>Perubahan</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="audit-tbody" data-testid="audit-tbody">
                                <tr class="table-loading">
                                    <td colspan="7" class="text-center text-muted">Belum ada data log.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <nav id="audit-paginasi" data-testid="audit-paginasi"></nav>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal detail baris (perbandingan sebelum & sesudah) --}}
    <div class="modal fade" id="modal-detail-audit" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Entri Log</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="audit-detail-body" data-testid="audit-detail-body">
                    <div class="text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat detail…</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script src="{{ asset('js/dataspasial/audit.js') }}"></script>
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.AuditDataSpasial) return;
            window.AuditDataSpasial.init({
                dataUrl: @json(route('dataspasial.audit.data')),
                detailUrl: @json(route('dataspasial.audit.detail', ['id' => '__ID__'])),
                exportUrl: @json(route('dataspasial.audit.ekspor')),
                desaUrl: @json(route('dataspasial.audit.data')),
            });
        });
    </script>
@endpush
