@include('layouts.components.choice_tahun', [
'url' => url('api/v1/web/statistik/' . strtolower($content[1]) . '/tahun'),
'default_id' => $content[2]
])

<div class="container" id="statistik_result" style="margin-top: 100px;">
    <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s">
        <h1 class="mb-3">
            Data Statistik {{ ucwords($content[1]) }} 
            <span class="text-primary">
                {{ ucwords(str_replace('-', ' ',$content[2])) }}
            </span>
        </h1>
    </div>
    <div class="col-lg-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <div class="row">
                    <div class="col-auto">
                        <a class="btn btn-sm btn-secondary" data-bs-toggle="collapse" href="#collapse-filter" role="button"
                            aria-expanded="true" aria-controls="collapse-filter">
                            <i class="fas fa-filter"></i>
                        </a>
                    </div>
    
                    <div class="col-auto">
                        <button id="btn-grafik" class="btn btn-sm btn-success btn-block btn-sm" data-toggle="collapse"
                            href="#grafik-statistik" role="button" aria-expanded="false" aria-controls="grafik-statistik">
                            <i class="fas fa-chart-bar"></i> Grafik
                        </button>
                    </div>
                    <div class="col-auto">
                        <button id="btn-pie" class="btn btn-sm btn-warning btn-block btn-sm" data-toggle="collapse"
                            href="#pie-statistik" role="button" aria-expanded="false" aria-controls="pie-statistik">
                            <i class="fas fa-chart-pie"></i> Chart
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="collapse-filter" class="collapse">
                            <div class="row">
                                <div class="col-sm mb-3">
                                    <label for="tahun" class="form-label">Tahun</label>
                                    <select class="form-select" id="tahun"></select>
                                </div>
                
                                <div class="col-sm mb-3">
                                    <label for="bulan" class="form-label">Bulan</label>
                                    <select class="form-select" id="bulan">
                                        <option value=""></option>
                                        @for ($x = 1; $x <= 12; $x++) <option value="{{ $x }}">{{ bulan($x) }}</option>
                                            @endfor
                                    </select>
                                </div>
                            </div>
                
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <button type="button" id="reset" class="btn btn-secondary btn-sm w-100">
                                        <i class="fas fa-ban"></i> Reset
                                    </button>
                                </div>
                
                                <div class="col-sm-6 mb-3">
                                    <button type="button" id="filter" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
    
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="grafik-statistik" class="collapse">
                            <div class="chart" id="grafik">
                                <canvas id="barChart"></canvas>
                            </div>
                            <hr class="hr-chart">
                        </div>
    
                        <div id="pie-statistik" class="collapse">
                            <div class="chart" id="pie">
                                <canvas id="donutChart"></canvas>
                            </div>
                            <hr class="hr-chart">
                        </div>
                    </div>
                </div>
    
                <div class="table-responsive">
                    <table class="table table-striped cell-border" id="tabel-data">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th id="judul_kolom_nama" width="50%"></th>
                                <th colspan="2" class="dt-head-center">Jumlah</th>
                                <th colspan="2" class="dt-head-center">Laki - laki</th>
                                <th colspan="2" class="dt-head-center">Perempuan</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


@push('js')

<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/jquery/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/chart.js/Chart.bundle.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap5/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/plugins/choices/js/choices.min.js') }}"></script>
@include('statistik.chart')
<script nonce="{{ csp_nonce() }}">

    let data_grafik = [];
    let nama_desa = `{{ session('desa.nama_desa') }}`;
    let kategori = `{{ strtolower($content[1]) }}`;
    let default_id = `{{ $content[2] }}`;
    document.addEventListener("DOMContentLoaded", function (event) {

        $('#btn-grafik').on('click', function() {
            // Sembunyikan elemen "pie-statistik" jika sedang terlihat
            $("#pie-statistik").collapse('hide');
            // Tampilkan atau toggle elemen "grafik-statistik"
            $("#grafik-statistik").collapse('toggle');
        });
        
        $('#btn-pie').on('click', function() {
            // Sembunyikan elemen "grafik-statistik" jika sedang terlihat
            $("#grafik-statistik").collapse('hide');
            // Tampilkan atau toggle elemen "pie-statistik"
            $("#pie-statistik").collapse('toggle');
        });

        var statistik = $('#tabel-data').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: false,
            searching: false,
            paging: false,
            info: false,
            ajax: {
                url: `{{ url('api/v1/web/statistik') }}/${kategori}?filter[id]=${default_id}`,
                method: 'get',
                data: function(row) {
                    return {
                        "filter[bulan]": $("#bulan").val(),
                        "filter[tahun]": $("#tahun").val(),
                    };
                },
                dataSrc: function(json) {
                    if (json.data.length > 0) {
                        data_grafik = [];
                        json.data.forEach(function(item, index) {
                            data_grafik.push(item.attributes)
                        })

                        grafikPie()

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
                    targets: [2, 3, 4, 5, 6, 7],
                    className: 'dt-body-right',
                },
            ],
            columns: [{
                data: null,
            }, {
                data: function(data) {
                    return data.attributes.nama;
                },
            }, {
                data: function(data) {
                    return data.attributes.jumlah
                },
            }, {
                data: function(data) {
                    return data.attributes.persentase_jumlah;
                },
            }, {
                data: function(data) {
                    return data.attributes.laki_laki
                },
            }, {
                data: function(data) {
                    return data.attributes.persentase_laki_laki;
                },
            }, {
                data: function(data) {
                    return data.attributes.perempuan
                },
            }, {
                data: function(data) {
                    return data.attributes.persentase_perempuan;
                },
            }]
        });

        statistik.on('draw.dt', function() {
            console.log('masuk')
            var dataTable = $('#tabel-data').DataTable();
            var pageInfo = dataTable.page.info();
            var recordsTotal = dataTable.data().count();

            statistik.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                if ((recordsTotal - i) <= 3) {
                    cell.innerHTML = '';
                } else {
                cell.innerHTML = i + 1 + pageInfo.start;
                }
            });
        });

        $('#filter').on('click', function(e) {
            statistik.draw();
        });

        $(document).on('click', '#reset', function(e) {
            e.preventDefault();
            $('#tahun').val('').change();
            $('#bulan').val('').change(); $('#bulan').val('').change();
            statistik.ajax.reload();
        });

        const bulanSelect = document.getElementById('bulan');
        const bulanChoices = new Choices(bulanSelect, {
            searchEnabled: true,       // Mengaktifkan pencarian
            allowHTML: true,           // Memungkinkan penggunaan HTML di label
            placeholderValue: 'Pilih Bulan',
            noResultsText: 'Tidak ada hasil',
            shouldSort: false          // Tidak mengurutkan hasil secara otomatis
        });
        
    });
</script>
@endpush
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap5/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/choices/css/choices.min.css') }}">
<style nonce="{{ csp_nonce() }}">
    #barChart {
        min-height: 250px;
        height: 250px;
        max-height: 250px;
        max-width: 100%;
    }

    #donutChart {
        min-height: 250px;
        height: 250px;
        max-height: 250px;
        max-width: 100%;
    }

    hr.hr-chart {
        margin-right: -20px;
        margin-left: -20px;
    }
</style>
@endpush