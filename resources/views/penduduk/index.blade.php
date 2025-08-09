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
                            <button id="download-excel" type="button" class="btn btn-success btn-sm">
                                <i class="fa fa-file-excel"></i>
                                Excel
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            var urlPenduduk = new URL(`{{ config('app.databaseGabunganUrl') . '/api/v1/penduduk' }}`);

            var penduduk = $('#penduduk').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ordering: true,
                ajax: {
                    url: urlPenduduk,
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
                            "filter[bpjs_ketenagakerjaan]": $('#bpjs_ketenagakerjaan').val(),
                            "filter[akta_lahir]": $('#akta_lahir').val(),
                            "filter[akta_perkawinan]": $('#akta_perkawinan').val(),
                            "filter[clusterDesa.dusun]": $("#dusun option:selected").text(),
                            "filter[clusterDesa.rw]": $('#rw').val(),
                            "filter[clusterDesa.rt]": $('#rt').val(),
                            "filter[kode_kabupaten]": $('#filter_kabupaten').val(),
                            "filter[kode_kecamatan]": $('#filter_kecamatan').val(),
                            "filter[kode_desa]": $('#filter_desa').val(),
                            "filter[bantuan-penduduk]": $('#bantuan-penduduk').val(),
                            "filter[program_id]": $('#program_id').val(),
                            "kode_kecamatan": "{{ session('kecamatan.kode_kecamatan') ?? '' }}",
                            "config_desa": "{{ session('desa.id') ?? '' }}",
                            "chart-view": chart?.view ?? null,
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

                        if (json.data.length > 0 && json.meta && json.meta.all_data) {

                            // jika chart di akses dari halaman statistik penduduk
                            if (chart && chart.view) {
                                getDataset(json.meta.all_data, chart)
                                grafik()
                            }

                        }
                        return json.data;
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
                $('#collapse-filter input').val('');
                kriteria_belum_mengisi = null;
                kriteria_jumlah = null;
                kriteria_total = null;
                penduduk.ajax.reload();
            });

            $('#cetak').on('click', function() {
                window.open(`{{ url('penduduk/cetak') }}?${$.param(penduduk.ajax.params())}`, '_blank');
            });

            $('#download-excel').on('click', function() {
                downloadExcel();
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

        function getDataset(data, chart) {
            const kategori = chart.kategori;
            data_grafik = [];
            const grouped = {};
            judul = chart.nama

            const params = new URLSearchParams(window.location.search);
            const kategoriQuery = params.get('kategori');
            const namaQuery = params.get('nama');
            const isKategoriBantuan = kategoriQuery === 'bantuan';

            const getLabel = {
                'rentang-umur': attr => parseInt(attr.umur),
                'kategori-umur': attr => parseInt(attr.umur),
                'pendidikan-dalam-kk': attr => attr.pendidikan_kk,
                'pendidikan-sedang-ditempuh': attr => attr.pendidikan,
                'agama': attr => attr.agama,
                'jenis-kelamin': attr => attr.jenis_kelamin,
                'pekerjaan': attr => attr.pekerjaan,
                'status-perkawinan': attr => attr.status_kawin,
                'hubungan-dalam-kk': attr => attr.penduduk_hubungan,
                'warga-negara': attr => attr.warga_negara,
                'status-penduduk': attr => attr.penduduk_status,
                'golongan-darah': attr => attr.golongan_darah,
                'penyandang-cacat': attr => attr.cacat,
                'penyakit-menahun': attr => attr.namaSakitMenahun,
                'akseptor-kb': attr => attr.kb,
                'akta-kelahiran': attr => parseInt(attr.umur),
                'ktp': attr => attr.status_rekam_ktp,
                'asuransi-kesehatan': attr => attr.namaAsuransi,
                'status-covid': attr => null,
                'suku': attr => attr.suku,
                'bpjs-ketenagakerjaan': attr => attr.bpjs_ketenagakerjaan,
                'status-kehamilan': attr => attr.statusHamil,
                ...(isKategoriBantuan && {
                    [kategori]: attr => namaQuery
                }),
            };

            const isMatch = {
                'rentang-umur': (label) => {
                    const [awal, akhir] = judul.match(/\d+/g).map(Number);
                    return label >= awal && label <= akhir;
                },
                'kategori-umur': (label) => label === judul,
                'pendidikan-dalam-kk': (label) => label === judul,
                'pendidikan-sedang-ditempuh': (label) => label === judul,
                'agama': (label) => label === judul,
                'jenis-kelamin': (label) => label === judul,
                'pekerjaan': (label) => label === judul,
                'status-perkawinan': (label) => label === judul,
                'hubungan-dalam-kk': (label) => label === judul,
                'warga-negara': (label) => label === judul,
                'status-penduduk': (label) => label === judul,
                'golongan-darah': (label) => label === judul,
                'penyandang-cacat': (label) => label === judul,
                'penyakit-menahun': (label) => label === judul,
                'akseptor-kb': (label) => label === judul,
                'akta-kelahiran': (label) => {
                    const [awal, akhir] = judul.match(/\d+/g).map(Number);
                    return label >= awal && label <= akhir;
                },
                'ktp': (label) => label === judul,
                'asuransi-kesehatan': (label) => label === judul,
                'status-covid': (label) => label === judul,
                'suku': (label) => label === judul,
                'bpjs-ketenagakerjaan': (label) => label === judul,
                'status-kehamilan': (label) => label === judul,
                ...(isKategoriBantuan && {
                    [kategori]: (label) => label === judul
                }),
            };

            data.forEach(item => {
                const attr = item;
                const kode = attr.kode_kecamatan;
                const nama = attr.nama_kecamatan;

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

        // Function to download Excel
        async function downloadExcel() {
            try {
                const header = @include('layouts.components.header_bearer_api_gabungan');
                // Check if there's data to download
                const tableData = $('#penduduk').DataTable();
                const info = tableData.page.info();
                const totalData = info.recordsTotal;
                if (totalData === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak Ada Data',
                        text: 'Tidak ada data penduduk untuk diunduh. Silakan periksa filter Anda.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Show loading state
                const $btnExcel = $('#download-excel');
                $btnExcel.prop('disabled', true).html(
                    '<i class="fa fa-spinner fa-spin"></i> Downloading...');

                // Prepare URL for download
                const downloadUrl = new URL(
                    `{{ config('app.databaseGabunganUrl') }}/api/v1/penduduk/download`);

                // Gunakan fungsi data yang sama persis dengan DataTable untuk konsistensi
                const filterParams = tableData.ajax.params();

                // Remove pagination parameters since we want all data
                delete filterParams['page[size]'];
                delete filterParams['page[number]'];

                // Handle umur filter - convert object to separate min/max parameters for backend
                if (filterParams['filter[umur]'] && typeof filterParams['filter[umur]'] === 'object') {
                    const umurObj = filterParams['filter[umur]'];

                    // Create separate parameters for min and max
                    if (umurObj.min && umurObj.min !== '') {
                        filterParams['filter[umur][min]'] = umurObj.min;
                    }
                    if (umurObj.max && umurObj.max !== '') {
                        filterParams['filter[umur][max]'] = umurObj.max;
                    }
                    if (umurObj.satuan) {
                        filterParams['filter[umur][satuan]'] = umurObj.satuan;
                    }

                    // Remove the original object parameter
                    delete filterParams['filter[umur]'];
                }

                // Convert filterParams to URLSearchParams for proper encoding
                const urlParams = new URLSearchParams();
                Object.keys(filterParams).forEach(key => {
                    const value = filterParams[key];
                    if (value !== null && value !== undefined && value !== '' && value !== 'null') {
                        urlParams.append(key, value);
                    }
                });

                urlParams.append('totalData', totalData);

                // Make fetch request
                const response = await fetch(downloadUrl, {
                    method: 'POST',
                    headers: {
                        ...header,
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    },
                    body: urlParams
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`HTTP ${response.status}: ${errorText}`);
                }

                // Check if response is actually a file
                const contentType = response.headers.get('content-type');
                if (!contentType || (!contentType.includes(
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') && !
                        contentType.includes('application/vnd.ms-excel'))) {
                    throw new Error('Response is not a valid Excel file');
                }

                // Get filename from response headers or generate one
                const contentDisposition = response.headers.get('content-disposition');
                let filename = 'data_penduduk.xlsx';
                if (contentDisposition) {
                    const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(contentDisposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, '');
                    }
                } else {
                    // Generate filename with timestamp
                    const now = new Date();
                    const timestamp = now.toISOString().slice(0, 19).replace(/[-:T]/g, '');
                    filename = `data_penduduk_${timestamp}.xlsx`;
                }

                // Create blob and download
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: `File Excel "${filename}" berhasil diunduh`,
                    timer: 3000,
                    showConfirmButton: false
                });

            } catch (error) {
                console.error('Download error:', error);

                // Show error message with SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Download!',
                    html: `
                            <p>Terjadi kesalahan saat mengunduh file Excel:</p>
                            <p><small>${error.message}</small></p>
                            <p>Silakan coba lagi atau hubungi administrator.</p>
                        `,
                    confirmButtonText: 'OK'
                });
            } finally {
                // Reset button state
                const $btnExcel = $('#download-excel');
                $btnExcel.prop('disabled', false).html('<i class="fa fa-file-excel"></i> Excel');
            }
        }
    </script>
@endsection
