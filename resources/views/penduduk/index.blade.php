@extends('layouts.index')
@include('layouts.components.selec2_penduduk_referensi')
@include('layouts.components.selec2_wilayah_referensi')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/progressive-image/progressive-image.css') }}">
@endpush

@section('title', 'Data Penduduk')

@section('content_header')
    <h1>Data Penduduk</h1>
@stop

@section('content')
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
                                            <label>Status Penduduk</label>
                                            <select class="select2 form-control-sm" id="status" name="status"
                                                data-placeholder="Semua Status" style="width: 100%;">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Status Dasar</label>
                                            <select class="select2 form-control-sm" id="status-dasar" name="status-dasar"
                                                data-placeholder="Semua Status Dasar" style="width: 100%;">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Jenis Kelamin</label>
                                            <select class="select2 form-control-sm" id="sex" name="sex"
                                                data-placeholder="Semua Jenis Kelamin" style="width: 100%;">
                                            </select>
                                        </div>
                                    </div>
                                    {{-- TODO: Aktifikan jika digunakan filter untuk tingkat dusun --}}
                                    {{-- <div class="col-sm">
                                        <div class="form-group">
                                            <label>Pilih Dusun</label>
                                            <select class="select2 form-control-sm" id="dusun" name="dusun"
                                                data-placeholder="Semua Dusun" style="width: 100%;">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Pilih RW</label>
                                            <select class="select2 form-control-sm" id="rw" name="rw"
                                                data-placeholder="Semua RW" style="width: 100%;" disabled>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Pilih RT</label>
                                            <select class="select2 form-control-sm" id="rt" name="rt"
                                                data-placeholder="Semua RT" style="width: 100%;" disabled>
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
                        <table class="table table-striped" id="penduduk">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Aksi</th>
                                    <th>Foto</th>
                                    <th>NIK</th>
                                    <th>Tag ID Card</th>
                                    <th>Nama</th>
                                    <th>No. KK</th>
                                    <th>Nama Ayah</th>
                                    <th>Nama Ibu</th>
                                    <th>No. Rumah Tangga</th>
                                    <th>Alamat</th>
                                    <th>Dusun</th>
                                    <th>RW</th>
                                    <th>RT</th>
                                    <th>Pendidikan dalam KK</th>
                                    <th>Umur</th>
                                    <th>Pekerjaan</th>
                                    <th>Kawin</th>
                                    <th>Tgl Peristiwa</th>
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

@push('js')
    <script src="{{ asset('assets/progressive-image/progressive-image.js') }}"></script>
@endpush

@section('js')
    <script>
        var penduduk = $('#penduduk').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            ajax: {
                url: `{{ url('api/v1/penduduk') }}`,
                method: 'get',
                data: function(row) {
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[sex]": $('#sex').val(),
                        "filter[status]": $('#status').val(),
                        "filter[status_dasar]": $('#status-dasar').val(),
                        "filter[clusterDesa.dusun]": $("#dusun option:selected").text(),
                        "filter[clusterDesa.rw]": $('#rw').val(),
                        "filter[clusterDesa.rt]": $('#rt').val(),
                        "filter[search]": row.search.value,
                        "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]
                            ?.name
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
                    targets: [0, 1, 3, 5, 6, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18],
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
                    searchable: false,
                    name: "aksi",
                    orderable: false,
                    data: function(data) {
                        return `<div class="btn-group open">
                            <button type="button" class="btn btn-social btn-flat btn-info btn-sm" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-arrow-circle-down"></i> Pilih Aksi</button>
                            <ul class="dropdown-menu" role="menu" style="">
                                <li>
                                    <a href="{{ url('penduduk/pindah') }}/${data.id}" class="btn btn-social btn-flat btn-block btn-sm"><i class="fas fa-exchange-alt"></i> Pindah Penduduk</a>
                                </li>
                            </ul>
                        </div>`
                    }
                },
                {
                    data: function(data) {
                        return `<a href="${data.attributes.urlFoto}" class="progressive replace kecil">
                                    <img class="preview" loading="lazy" src="{{ asset('assets/img/img-loader.gif') }}" alt="Foto Penduduk"/>
                                </a>`
                    }
                },
                {
                    data: function(data) {
                        return `<a title="Lihat Detail Biodata Penduduk" href="penduduk/${data.id}">${data.attributes.nik}</a>`
                    },
                    name: "nik"
                },
                {
                    data: "attributes.tag_id_card",
                    name: "tag_id_card",
                    searchable: false,
                },
                {
                    data: "attributes.nama",
                    name: "nama"
                },
                {
                    data: function(data) {
                        if (data.attributes.keluarga?.no_kk) {
                            return `<a title="Lihat Detail Biodata Keluarga" href="keluarga/detail/${data.attributes.keluarga.no_kk}">${data.attributes.keluarga.no_kk}</a>`
                        } else {
                            return null
                        }
                    },
                    name: "keluarga.no_kk",
                    searchable: true,
                },
                {
                    data: "attributes.nama_ayah"
                },
                {
                    data: "attributes.nama_ibu"
                },
                {
                    data: function(data) {
                        return data.attributes.rtm?.no_kk ?? null
                    },
                },
                {
                    data: function(data) {
                        return data.attributes.keluarga?.alamat ?? null
                    },
                },
                {
                    data: function(data) {
                        return data.attributes.cluster_desa?.dusun ?? null
                    },
                },
                {
                    data: function(data) {
                        return data.attributes.cluster_desa?.rw ?? null
                    },
                },
                {
                    data: function(data) {
                        return data.attributes.cluster_desa?.rt ?? null
                    },
                },
                {
                    data: function(data) {
                        return data.attributes.pendidikan_k_k?.nama ?? null
                    },
                },
                {
                    data: "attributes.umur"
                },
                {
                    data: function(data) {
                        return data.attributes.pekerjaan?.nama ?? null
                    },
                },
                {
                    data: function(data) {
                        return data.attributes.status_kawin?.nama ?? null
                    },
                },
                {
                    data: function(data) {
                        return data.attributes.log_penduduk?.tgl_peristiwa ?? null
                    },
                },
                {
                    data: "attributes.created_at"
                }
            ],
            order: [
                [5, 'asc']
            ]
        })

        penduduk.on('draw.dt', function() {
            var PageInfo = $('#penduduk').DataTable().page.info();
            penduduk.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });

        $('#filter').on('click', function(e) {
            penduduk.draw();
        });

        $(document).on('click', '#reset', function(e) {
            e.preventDefault();
            $('#sex').val('').change();
            $('#status').val('').change();
            $('#status-dasar').val('').change();
            $('#dusun').val('').change();
            $('#rw').val('').change();
            $('#rt').val('').change();

            penduduk.ajax.reload();
        });

        $('#cetak').on('click', function() {
            window.open(`{{ url('penduduk/cetak') }}?${$.param(penduduk.ajax.params())}`, '_blank');
        });
    </script>
@endsection
