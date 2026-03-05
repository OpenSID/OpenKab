@extends('layouts.index')
@include('layouts.components.selec2_penduduk_referensi')
@include('layouts.components.selec2_wilayah_referensi')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/progressive-image/progressive-image.css') }}">
@endpush

@section('title', 'Data Keluarga')

@section('content_header')
    <h1>Data Keluarga</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @if (isset($chart['view']))
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <div class="chart" id="grafik">
                                <canvas id="barChart"></canvas>
                            </div>
                            <hr class="hr-chart">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-3">
                            <a class="btn btn-sm btn-secondary" data-toggle="collapse" href="#collapse-filter"
                                role="button" aria-expanded="false" aria-controls="collapse-filter">
                                <i class="fas fa-filter"></i>
                            </a>
                            <button id="cetak" type="button" class="btn btn-primary btn-sm">
                                <i class="fa fa-print"></i>
                                Cetak
                            </button>
                            <x-excel-download-button :download-url="config('app.databaseGabunganUrl') . '/api/v1/keluarga/download'" table-id="keluarga" filename="data_keluarga" />
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
                                            <label>Status KK</label>
                                            <select class="select2 form-control-sm width-100" id="status_kk" name="status"
                                                data-placeholder="Semua Status">
                                                @foreach (App\Models\Enums\StatusDasarKKEnum::all() as $key => $value)
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Jenis Kelamin</label>
                                            <select class="select2 form-control-sm width-100" id="sex" name="sex"
                                                data-placeholder="Semua Jenis Kelamin">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Kelas Sosial</label>
                                            <select class="select2-filter form-control-sm width-100" id="kelas_sosial"
                                                name="kelas_sosial" data-option='{!! json_encode(App\Models\Enums\KelasSosialEnum::select2()) !!}'
                                                data-placeholder="Semua Kelas Sosial">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Bantuan</label>
                                            <select id="bantuan-keluarga" class="form-control select2-filter"
                                                data-option='{!! json_encode(App\Models\Enums\StatusEnum::select2()) !!}' placeholder="Pilih Bantuan">
                                            </select>
                                        </div>
                                    </div>
                                </div>
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
                                    {{-- TODO: Aktifikan jika digunakan filter untuk tingkat dusun --}}
                                    {{-- <div class="col-sm">
                                        <div class="form-group">
                                            <label>Pilih Dusun</label>
                                            <select class="select2 form-control-sm width-100" id="dusun" name="dusun"
                                                data-placeholder="Semua Dusun">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Pilih RW</label>
                                            <select class="select2 form-control-sm width-100" id="rw" name="rw"
                                                data-placeholder="Semua RW" disabled>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Pilih RT</label>
                                            <select class="select2 form-control-sm width-100" id="rt" name="rt"
                                                data-placeholder="Semua RT" disabled>
                                            </select>
                                        </div>
                                    </div> --}}
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
                        @if ($judul)
                            <h4 class="text-center">{{ $judul }}</h4>
                        @endif
                        <table class="table table-striped" id="keluarga">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Foto</th>
                                    <th>No. KK</th>
                                    <th>Kepala Keluarga</th>
                                    <th>NIK</th>
                                    <th>Tag ID Card</th>
                                    <th>Jumlah Anggota</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Alamat</th>
                                    <th>Dusun</th>
                                    <th>RW</th>
                                    <th>RT</th>
                                    <th>Tgl Terdaftar</th>
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

    @if (isset($chart['view']))
        @include('penduduk.chart')
    @endif

    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            const header = @include('layouts.components.header_bearer_api_gabungan');
            const filterDefault = {!! json_encode($filters) !!}
            const chart = {!! json_encode($chart) !!}
            let kriteria_jumlah = filterDefault['jumlah'] ?? null
            let kriteria_belum_mengisi = filterDefault['belum_mengisi'] ?? null
            let kriteria_total = filterDefault['total'] ?? null

            var keluarga = $('#keluarga').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ordering: true,
                ajax: {
                    url: `{{ config('app.databaseGabunganUrl') . '/api/v1/keluarga' }}`,
                    headers: header,
                    method: 'get',
                    data: function(row) {
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            "filter[jumlah]": kriteria_jumlah,
                            "filter[belum_mengisi]": kriteria_belum_mengisi,
                            "filter[total]": kriteria_total,
                            "filter[sex]": $('#sex').val(),
                            "filter[status]": $('#status_kk').val(),
                            "filter[kelas_sosial]": $('#kelas_sosial').val(),
                            "filter[kode_kabupaten]": $('#filter_kabupaten').val(),
                            "filter[kode_kecamatan]": $('#filter_kecamatan').val(),
                            "filter[kode_desa]": $('#filter_desa').val(),
                            "filter[bantuan-keluarga]": $('#bantuan-keluarga').val(),
                            "kode_kecamatan": "{{ session('kecamatan.kode_kecamatan') ?? '' }}",
                            "config_desa": "{{ session('desa.id') ?? '' }}",
                            "filter[search]": row.search.value,
                            "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]
                                    ?.column]
                                ?.name
                        };
                    },
                    dataSrc: function(json) {
                        json.recordsTotal = json.meta.pagination.total
                        json.recordsFiltered = json.meta.pagination.total

                        if (json.data.length > 0) {

                            // jika chart di akses dari halaman statistik keluarga
                            if (chart && chart.view) {
                                getDataset(json.data, chart)
                                grafik()
                            }


                            return json.data;
                        }

                        return false;
                    },
                },
                columnDefs: [{
                        targets: '_all',
                        className: 'text-nowrap',
                    },
                    {
                        targets: [0, 1, 2, 3, 6, 6, 7, 8, 9, 10, 11, 12],
                        orderable: false,
                        searchable: false,
                    },
                ],
                columns: [{
                        data: null,
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: function(data) {
                            let hrefTag = data.attributes.urlFoto ? 'href=' + data.attributes
                                .urlFoto : `href="{{ asset('assets/img/avatar.png') }}"`;
                            return `<a ${hrefTag} class="progressive replace kecil">
                                    <img class="preview" loading="lazy" src="{{ asset('assets/img/img-loader.gif') }}" alt="Foto Penduduk"/>
                                </a>`
                        }
                    },
                    {
                        data: function(data) {
                            if (data.attributes.no_kk) {
                                return `<a title="Lihat Detail Biodata Keluarga" href="keluarga/detail/${data.attributes.no_kk}">${data.attributes.no_kk}</a>`
                            } else {
                                return null
                            }
                        },
                        name: "no_kk",
                        searchable: true,
                    },
                    {
                        data: "attributes.nama_kk",
                        name: "nama",
                        searchable: false,
                    },
                    {
                        data: function(data) {
                            return `<a title="Lihat Detail Biodata Penduduk" href="penduduk/${data.attributes.id_kepala}">${data.attributes.nik_kepala}</a>`
                        },
                        name: "nik"
                    },
                    {
                        data: "attributes.tag_id_card",
                        name: "tag_id_card",
                    },
                    {
                        data: "attributes.jumlah_anggota",
                        sortable: false,
                        searchable: false,
                    },
                    {
                        data: "attributes.sex",
                        sortable: false,
                        searchable: false,
                    },
                    {
                        data: "attributes.alamat",
                        sortable: false,
                        searchable: false,
                    },
                    {
                        data: "attributes.dusun",
                        sortable: false,
                        searchable: false,
                    },
                    {
                        data: "attributes.jumlah_anggota",
                        sortable: false,
                        searchable: false,
                    },
                    {
                        data: "attributes.rw",
                        sortable: false,
                        searchable: false,
                    },
                    {
                        data: "attributes.tgl_terdaftar",
                        name: "tgl_terdaftar",
                        searchable: false,
                    }
                ],
                order: [
                    [2, 'asc']
                ]
            })

            keluarga.on('draw.dt', function() {
                var PageInfo = $('#keluarga').DataTable().page.info();
                keluarga.column(0, {
                    page: 'current'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + PageInfo.start;
                });
            });

            $('#filter').on('click', function(e) {
                keluarga.draw();
            });

            $(document).on('click', '#reset', function(e) {
                e.preventDefault();
                $('#sex').val('').change();
                $('#status').val('').change();
                $('#status-dasar').val('').change();
                $('#filter_kabupaten').val('').change();
                $('#filter_kecamatan').val('').change();
                $('#filter_desa').val('').change();
                $('.select2-filter').val('').change();
                kriteria_belum_mengisi = null;
                kriteria_jumlah = null;
                kriteria_total = null;

                keluarga.ajax.reload();
            });

            $('#cetak').on('click', function() {
                window.open(`{{ url('keluarga/cetak') }}?${$.param(keluarga.ajax.params())}`, '_blank');
            });

            $('#status_kk').select2({
                theme: 'bootstrap4',
                allowClear: true,
                placeholder: "Semua Status",
            });

            $('select.select2-filter').each(function() {
                $(this).select2({
                    width: '100%',
                    theme: 'bootstrap4',
                    placeholder: $(this).attr('placeholder'),
                    allowClear: true,
                    data: $(this).data('option') ?? null,
                })
            });

            for (let i in filterDefault) {
                $(`#${i}`).val(filterDefault[i]).trigger('change');
            }

            @if ($filters['kode_kabupaten'] ?? false)
                $('a[href="#collapse-filter"]').click();
            @endif
        });

        function getDataset(data, chart) {
            const kategori = chart.kategori;
            data_grafik = [];
            const grouped = {};
            judul = chart.nama

            const getLabel = {
                'kelas-sosial': attr => attr.kelas_sosial,
            };

            const isMatch = {
                'kelas-sosial': (label) => label === judul,
            };

            data.forEach(item => {
                const attr = item.attributes;
                const config = attr.config || {};
                const kode = config.kode_kecamatan;
                const nama = config.nama_kecamatan;

                if (!grouped[kode]) {
                    grouped[kode] = {
                        nama: nama,
                        total: 0
                    };
                }

                const label = getLabel[kategori]?.(attr);

                if (label !== undefined && isMatch[kategori]?.(label)) {
                    grouped[kode].total += 1;
                }
            });

            data_grafik = Object.entries(grouped).map(([kode, val]) => ({
                label: val.nama,
                value: val.total
            }));
        }
    </script>
@endsection
