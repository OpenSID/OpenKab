<div class="container" id="statistik_result">
    <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s">
        <h1 class="mb-3">Data Statistik {{ $desa->nama_desa }}</h1>
        <p>Informasi ini menampilkan jumlah statistik yang ada pada portal kelurahan masing - masing bersifat realtime.</p>
    </div>
    <div class="row g-4">
        @foreach ($categoriesItems as $item)
            @include('web.partials.category_item', $item)
        @endforeach
    </div>

    <div class="row">
        <div class="col-3">
            @foreach ($groupStatistik as $index => $stat)
            <div class="panel-group" role="tablist">
                <div class="panel panel-default">
                  <div class="panel-heading" role="tab" id="collapseListGroupHeading1-{{$stat['text']}}">
                        <a class="collapsed  d-flex justify-content-between fs-5" data-bs-toggle="collapse" href="#collapseListGroup1-{{$stat['text']}}" aria-expanded="false" aria-controls="collapseListGroup1">
                            <span> <i class="fa {{ $stat['icon'] }}"></i> Statistik {{ $stat['text'] }} </span>
                        <i class="fa {{ !$index ? 'fa-angle-up' : 'fa-angle-down' }}"></i>
                      </a>
                  </div>
                  <div id="collapseListGroup1-{{$stat['text']}}" class="panel-collapse collapse {{ !$index ? 'show' : '' }}" role="tabpanel" aria-labelledby="collapseListGroupHeading1-{{$stat['text']}}">
                    <ul class="list-group">
                    @foreach ($stat['items'] as $key => $item)
                        <li class="list-group-item" role="button" data-id="{{ $key }}" data-configdesa="{{ $desa->id }}" data-kategori="{{ $stat['key'] }}">{{ $item }}</li>
                    @endforeach
                    </ul>
                  </div>
                </div>
              </div>
            @endforeach
        </div>
        <div class="col-9">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-2">
                            <button id="btn-grafik" class="btn btn-sm btn-success btn-block btn-sm" data-bs-toggle="collapse"
                                href="#grafik-statistik" role="button" aria-expanded="false"
                                aria-controls="grafik-statistik">
                                <i class="fas fa-chart-bar"></i> Grafik
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button id="btn-pie" class="btn btn-sm btn-warning btn-block btn-sm" data-bs-toggle="collapse"
                                href="#pie-statistik" role="button" aria-expanded="false" aria-controls="pie-statistik">
                                <i class="fas fa-chart-pie"></i> Chart
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="grafik-statistik" class="collapse4">
                                <div class="chart" id="grafik">
                                    <canvas id="barChart"></canvas>
                                </div>
                                <hr class="hr-chart">
                            </div>

                            <div id="pie-statistik" class="collapse4">
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
</div>

