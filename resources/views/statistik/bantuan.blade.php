@extends('layouts.index')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/progressive-image/progressive-image.css') }}">
@endpush

@section('title', 'Data Statistik')

@section('content_header')
    <h1>Data Statistik Bantuan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped cell-border" id="statistik-bantuan">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th id="judul-sasaran"></th>
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
@endsection

@push('js')
    <script src="{{ asset('assets/progressive-image/progressive-image.js') }}"></script>
@endpush

@section('js')
    <script>
        var statistik = $('#statistik-bantuan').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: false,
            ajax: {
                url: `{{ url('api/v1/statistik/bantuan') }}`,
                method: 'get',
                data: function(row) {
                    return {
                        "filter[id]": 6,
                    };
                },
                dataSrc: function(json) {
                    json.statistik = json.data[0].attributes.sasaran
                    json.recordsTotal = json.meta.pagination.total
                    json.recordsFiltered = json.meta.pagination.total

                    $('#judul-sasaran').html(json.data[0].attributes.nama_sasaran);

                    return json.data[0].attributes.statistik
                },
            },
            columns: [{
                data: null,
            }, {
                data: "nama"
            }, {
                data: "jumlah",
                className: 'dt-body-right',
            }, {
                data: function(data) {
                    return data.persentase_jumlah.toFixed(2) + '%';
                },
                className: 'dt-body-right',
            }, {
                data: function(data) {
                    return data.laki_laki
                },
                className: 'dt-body-right',
            }, {
                data: function(data) {
                    return data.persentase_laki_laki.toFixed(2) + '%';
                },
                className: 'dt-body-right',
            }, {
                data: function(data) {
                    return data.perempuan
                },
                className: 'dt-body-right',
            }, {
                data: function(data) {
                    return data.persentase_perempuan.toFixed(2) + '%';
                },
                className: 'dt-body-right',
            }]
        })

        statistik.on('draw.dt', function() {
            var PageInfo = $('#statistik-bantuan').DataTable().page.info();
            statistik.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });
    </script>
@endsection
