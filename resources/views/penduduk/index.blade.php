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
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @include('penduduk.filter_form')
                    </div>
                    <div class="table-responsive">
                        @if ($judul)
                            <h4 class="text-center">{{ $judul }}</h4>
                        @endif
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
@include('components.wilayah_filter_js')
@push('js')
    <script src="{{ asset('assets/progressive-image/progressive-image.js') }}"></script>
@endpush

@section('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            const header = @include('layouts.components.header_bearer_api_gabungan');
            const filterDefault = {!! json_encode($filters) !!}
            let kriteria_jumlah = filterDefault['jumlah'] ?? null
            let kriteria_belum_mengisi = filterDefault['belum_mengisi'] ?? null
            let kriteria_total = filterDefault['total'] ?? null

            var penduduk = $('#penduduk').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ordering: true,
                ajax: {
                    url: `{{ config('app.databaseGabunganUrl') . '/api/v1/penduduk' }}`,
                    headers: header,
                    method: 'get',
                    data: function(row) {
                        const params = {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            "filter[jumlah]": kriteria_jumlah,
                            "filter[belum_mengisi]": kriteria_belum_mengisi,
                            "filter[total]": kriteria_total,
                            "filter[ktp]": $('#ktp').val(),
                            "filter[sex]": $('#sex').val(),
                            "filter[status]": $('#status').val(),
                            "filter[kk_level]": $('#kk_level').val(),
                            "filter[warganegara_id]": $('#warganegara_id').val(),
                            "filter[status_dasar]": $('#status-dasar').val(),
                            "filter[golongan_darah_id]": $('#golongan_darah_id').val(),
                            "filter[cacat_id]": $('#cacat_id').val(),
                            "filter[sakit_menahun_id]": $('#sakit_menahun_id').val(),
                            "filter[cara_kb_id]": $('#cara_kb_id').val(),
                            "filter[id_asuransi]": $('#id_asuransi').val(),
                            "filter[hamil]": $('#hamil').val(),
                            "filter[suku]": $('#suku').val(),
                            "filter[status_covid]": $('#status_covid').val(),
                            "filter[status_rekam]": $('#status_rekam').val(),
                            "filter[pendidikan_kk_id]": $('#pendidikan_kk_id').val(),
                            "filter[pendidikan_sedang_id]": $('#pendidikan_sedang_id').val(),
                            "filter[pekerjaan_id]": $('#pekerjaan_id').val(),
                            "filter[status_kawin]": $('#status_kawin').val(),
                            "filter[id_kk]": $('#id_kk').val(),
                            "filter[tag_id_card]": $("#tag_id_card").val(),
                            "filter[agama_id]": $('#agama_id').val(),
                            "filter[clusterDesa.dusun]": $("#dusun option:selected").text(),
                            "filter[clusterDesa.rw]": $('#rw').val(),
                            "filter[clusterDesa.rt]": $('#rt').val(),
                            "filter[kode_kabupaten]": $('#filter_kabupaten').val(),
                            "filter[kode_kecamatan]": $('#filter_kecamatan').val(),
                            "filter[kode_desa]": $('#filter_desa').val(),
                            "filter[bantuan-penduduk]": $('#bantuan-penduduk').val(),
                            "kode_kecamatan": "{{ session('kecamatan.kode_kecamatan') ?? '' }}",
                            "config_desa": "{{ session('desa.id') ?? '' }}",
                            "filter[search]": row.search.value,
                            "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row
                                    .order[0]
                                    ?.column]
                                ?.name
                        };
                        const umurMin = $('#filter_umur_dari').val();
                        const umurMax = $('#filter_umur_sampai').val();
                        const umurObj = {
                            min: '',
                            max: '',
                            satuan: 'tahun'
                        };
                        if (umurMin != '') {
                            umurObj.min = umurMin;
                        }
                        if (umurMax != '') {
                            umurObj.max = umurMax;
                        }
                        if (umurObj.min || umurObj.max) {
                            params['filter[umur]'] = umurObj;
                        }
                        return params;
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
                        targets: [0, 1, 2, 3, 6, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18],
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
                        visible: `{{ $canedit }}`,
                        data: function(data) {
                            var pindah = (data.attributes.status_dasar == 1) ? '' : 'disabled';
                            let pindahMenu = `<li>
                                    <a href="{{ url('penduduk/pindah') }}/${data.id}" class="btn btn-social btn-flat btn-block btn-sm ${pindah} "><i class="fas fa-exchange-alt"></i> Pindah Penduduk</a>
                                </li>`;
                            return `<div class="btn-group open">
                            <button type="button" class="btn btn-social btn-flat btn-info btn-sm" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-arrow-circle-down"></i> Pilih Aksi</button>
                            <ul class="dropdown-menu" role="menu">
                                ${pindahMenu}
                            </ul>
                        </div>`;
                        }
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
                            return `<a title="Lihat Detail Biodata Penduduk" href="penduduk/${data.id}">${data.attributes.nik}</a>`
                        },
                        name: "nik"
                    },
                    {
                        data: "attributes.tag_id_card",
                        name: "tag_id_card",
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
                $('#filter_kabupaten').val('').change();
                $('#filter_kecamatan').val('').change();
                $('#filter_desa').val('').change();
                $('.select2-filter').val('').change();
                kriteria_belum_mengisi = null;
                kriteria_jumlah = null;
                kriteria_total = null;
                penduduk.ajax.reload();
            });

            $('#cetak').on('click', function() {
                window.open(`{{ url('penduduk/cetak') }}?${$.param(penduduk.ajax.params())}`, '_blank');
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


        });
    </script>
@endsection
