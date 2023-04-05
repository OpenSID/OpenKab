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
                        <table class="table table-striped" id="statistik-bantuan">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>{{ $sasaran ?? '' }}</th>
                                    <th>Jumlah</th>
                                    <th>Laki - laki</th>
                                    <th>Perempuan</th>
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

{{-- @section('js')
    <script>
        var statistik = $('#statistik-bantuan').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: false,
            ajax: {
                url: `{{ url('api/v1/statistik/bantuan') }}`,
                method: 'get',
                dataSrc: function(json) {
                    json.recordsTotal = json.meta.pagination.total
                    json.recordsFiltered = json.meta.pagination.total

                    return json.data
                },
            },
            columns: [{
                data: null,
            }, {
                data: 'sasaran'
            }, {
                data: 'jumlah'
            }, {
                data: 'laki_laki'
            }, {
                data: 'perempuan'
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
@endsection --}}
