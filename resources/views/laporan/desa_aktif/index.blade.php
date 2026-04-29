@extends('layouts.index')

@section('title', $title)

@section('content_header')
<h1>{{ $title }}</h1>
@stop

@section('content')
@include('partials.breadcrumbs')
<div class="row">
    <div class="col-lg-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <button id="cetak" type="button" class="btn btn-primary btn-sm" data-url=""><i
                        class="fa fa-print"></i>
                    Cetak</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="desaAktifTable" style="width:100%">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Desa</th>
                                <th class="text-center">Artikel<br>Terbit</th>
                                <th class="text-center">Jumlah<br>Akses</th>
                                <th class="text-center">Jml Surat</th>
                                <th class="text-center">Penduduk</th>
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
<script nonce="{{ csp_nonce() }}">
    document.addEventListener("DOMContentLoaded", function(event) {
        const desaAktifTable = $('#desaAktifTable').DataTable({
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            ordering: false,
            pageLength: 10,
            ajax: function(data, callback, settings) {
                let params = {
                    draw: data.draw,
                    start: data.start,
                    length: data.length,
                };
                params['page[size]'] = data.length;
                params['page[number]'] = Math.floor(data.start / data.length) + 1;
                params['filter[search]'] = data.search ? data.search.value : '';
                params['filter[kode_kabupaten]'] = "{{ session('kabupaten.kode_kabupaten') ?? $identitasAplikasi['kode_kabupaten_api'] }}";
                params['filter[kode_kecamatan]'] = "{{ session('kecamatan.kode_kecamatan') ?? '' }}";

                 apiProxyGet('desa-aktif', params, function(response) {
                     var total = 0;
                     if (response.meta && response.meta.pagination) {
                         total = response.meta.pagination.total;
                         data.recordsTotal = total;
                         data.recordsFiltered = total;
                     }
                     callback({
                         draw: data.draw,
                         recordsTotal: total,
                         recordsFiltered: total,
                         data: response.data || []
                     });                     
                 });
            },
            columns: [{
                    data: null
                },
                {
                    data: 'attributes.nama_desa'
                },
                {
                    data: 'attributes.artikel',
                    className: 'text-center'
                },
                {
                    data: 'attributes.traffic',
                    className: 'text-center'
                },
                {
                    data: 'attributes.surat',
                    className: 'text-center'
                },
                {
                    data: 'attributes.penduduk',
                    className: 'text-center'
                },
            ],
            columnDefs: [{
                targets: 0,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            }],
            order: [
                [1, 'asc']
            ],
        });

        $('#cetak').on('click', function() {
            let url = new URL("{{ url('laporan/desa-aktif/cetak') }}");
            url.searchParams.append("search", $('input[aria-controls="desaAktifTable"]').val() ?? '');
            window.open(url.href, '_blank');
        });

    })
</script>
@endpush