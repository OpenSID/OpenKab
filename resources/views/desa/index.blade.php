@extends('layouts.index')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/progressive-image/progressive-image.css') }}">
@endpush

@section('title', 'Data Desa')

@section('content_header')
    <h1>Data Desa</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-3">
                            <a class="btn btn-sm btn-secondary" data-toggle="collapse" href="#collapse-filter" role="button"
                                aria-expanded="false" aria-controls="collapse-filter">
                                <i class="fas fa-filter"></i>
                            </a>
                            <button id="cetak" type="button" class="btn btn-primary btn-sm">
                                <i class="fa fa-print"></i>
                                Cetak
                            </button>
                            <x-excel-download-button :download-url="config('app.databaseGabunganUrl') . '/api/v1/wilayah/penduduk/download'" table-id="summary-penduduk" filename="data_desa" />
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="collapse-filter" class="collapse">
                                <div class="row">
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Kabupaten</label>
                                            <select name="Filter Kabupaten" id="filter_kabupaten" class="form-control-sm"
                                                title="Pilih {{ config('app.sebutanKab') }}">
                                                @if ($filters['kode_kabupaten'] ?? false)
                                                    <option value="{{ $filters['kode_kabupaten'] }}" selected>
                                                        {{ $filters['nama_kabupaten'] }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Kecamatan</label>
                                            <select name="Filter Kecamatan" id="filter_kecamatan" class="form-control"
                                                title="Pilih Kecamatan">
                                                @if ($filters['kode_kecamatan'] ?? false)
                                                    <option value="{{ $filters['kode_kecamatan'] }}" selected>
                                                        {{ $filters['nama_kecamatan'] }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>{{ config('app.sebutanDesa') }}</label>
                                            <select name="Filter Desa" id="filter_desa" class="form-control"
                                                title="Pilih {{ config('app.sebutanDesa') }}">
                                                @if ($filters['kode_desa'] ?? false)
                                                    <option value="{{ $filters['kode_desa'] }}" selected>
                                                        {{ $filters['nama_desa'] }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="btn-group btn-group-sm btn-block">
                                                    <button type="button" id="reset" class="btn btn-secondary"><span
                                                            class="fas fa-ban"></span></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="btn-group btn-group-sm btn-block">
                                                    <button type="button" id="filter" class="btn btn-primary"><span
                                                            class="fas fa-search"></span></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr class="mt-0">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="summary-penduduk">
                            <thead>
                                <tr>
                                    <th class="padat">No</th>
                                    <th>{{ config('app.sebutanDesa') }}</th>
                                    <th>Kecamatan</th>
                                    <th class="padat">Jumlah Penduduk</th>
                                    <th class="padat">Aksi</th>
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
@include('components.wilayah_filter_js')
@push('js')
    <script src="{{ asset('assets/progressive-image/progressive-image.js') }}"></script>
@endpush

@section('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            const header = @include('layouts.components.header_bearer_api_gabungan');
            let urlPenduduk = new URL(
                "{{ config('app.databaseGabunganUrl') . '/api/v1/wilayah/penduduk' }}");
            const pendudukDatatable = $('#summary-penduduk').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ordering: true,
                order: [
                    [1, 'asc']
                ],
                searchPanes: {
                    viewTotal: false,
                    columns: [0]
                },
                ajax: {
                    url: urlPenduduk,
                    headers: header,
                    method: 'get',
                    data: function(row) {
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            "filter[search]": row.search.value,
                            "filter[kode_kabupaten]": $("#filter_kabupaten").val() || '',
                            "filter[kode_kecamatan]": $("#filter_kecamatan").val() || '',
                            "filter[kode_desa]": $("#filter_desa").val() || '',
                            "sort": row.columns[row.order[0]?.column]?.name ?
                                (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row
                                    .order[0]
                                    ?.column]
                                ?.name : undefined
                        };
                    },
                    dataSrc: function(json) {
                        json.recordsTotal = json.meta.pagination.total
                        json.recordsFiltered = json.meta.pagination.total

                        return json.data
                    },
                },
                columnDefs: [{
                        targets: '_all',
                        className: 'text-nowrap',
                    },
                    {
                        targets: 0,
                        render: function(data, type, row, meta) {
                            var PageInfo = $('#summary-penduduk').DataTable().page
                                .info();
                            return PageInfo.start + meta.row + 1;
                        }
                    },
                    {
                        targets: 0,
                        orderable: false,
                        searchable: false,
                    },
                ],
                columns: [{
                        data: null,
                    },
                    {
                        data: "attributes.nama_desa",
                        name: "nama_desa",
                        orderable: true,
                    },
                    {
                        data: "attributes.nama_kecamatan",
                        name: "nama_kecamatan",
                        orderable: true,
                    },
                    {
                        data: "attributes.penduduk_count",
                        name: "penduduk_count",
                        orderable: true,
                        className: 'text-center'
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            const kodeDesa = (row.attributes && row.attributes.kode_desa) || row.id || '';
                            if (!kodeDesa) return '';

                            const website = (row.attributes && row.attributes.website) || '';
                            if (!website) {
                                return `<span class="sso-website-kosong text-muted" title="Isi URL website desa pada database gabungan agar akses SSO aktif">
                                    <i class="fas fa-info-circle"></i> URL website belum diisi
                                </span>`;
                            }

                            return `<button type="button" class="btn btn-sm btn-primary btn-sso-opensid" data-kode-desa="${kodeDesa}" title="Masuk ke OpenSID">
                                <i class="fas fa-sign-in-alt"></i> Masuk ke OpenSID
                            </button>`;
                        }
                    },
                ],
            })
            $('#tabel_penduduk_block').change(function(event) {
                pendudukDatatable.draw();
            })

            $('#filter').on('click', function(e) {
                pendudukDatatable.draw();
            });

            $(document).on('click', '#reset', function(e) {
                e.preventDefault();
                $('#filter_kabupaten').val('').change();
                $('#filter_kecamatan').val('').change();
                $('#filter_desa').val('').change();

                pendudukDatatable.ajax.reload();
            });

            $('#cetak').on('click', function() {
                window.open(`{{ url('desa/cetak') }}?${$.param(pendudukDatatable.ajax.params())}`,
                    '_blank');
            });

            const ssoCsrfToken = '{{ csrf_token() }}';
            const ssoGenerateUrl = '/sso/generate-session';

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-sso-opensid');
                if (!btn) return;

                e.preventDefault();

                const desaId = btn.getAttribute('data-kode-desa');
                if (!desaId) return;

                btn.disabled = true;

                fetch(ssoGenerateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': ssoCsrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ desa_id: desaId }),
                    })
                    .then(function(response) {
                        return response.json().then(function(data) {
                            return { ok: response.ok, status: response.status, data: data };
                        });
                    })
                    .then(function(result) {
                        if (result.data.status !== 'success') {                            
                            const reason = result.data.message || 'Autentikasi gagal. Silakan coba lagi.';
                            Swal.fire(
                                'Gagal',
                                reason,
                                'error'
                            );
                            return;
                        }

                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = result.data.redirect_url;
                        form.style.display = 'none';

                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'sso_token';
                        input.value = result.data.token;
                        form.appendChild(input);

                        document.body.appendChild(form);
                        form.submit();
                    })
                    .catch(function() {
                        Swal.fire(
                            'Gagal',
                            'Terjadi kesalahan. Silakan coba lagi.',
                            'error'
                        );
                    })
                    .finally(function() {
                        btn.disabled = false;
                    });
            });

            @if ($filters['kode_kabupaten'] ?? false)
                $('a[href="#collapse-filter"]').click();
            @endif
        });
    </script>
@endsection
