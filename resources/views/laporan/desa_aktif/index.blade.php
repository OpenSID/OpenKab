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
                <button id="export-excel" type="button" class="btn btn-success btn-sm"><i
                        class="fa fa-file-excel"></i>
                    Excel</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="desaAktifTable" style="width:100%">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Desa</th>
                                <th>Kecamatan</th>
                                <th class="text-center">Artikel<br>Terbit</th>
                                <th class="text-center">Jumlah<br>Akses</th>
                                <th class="text-center">Jml Surat</th>
                                <th class="text-center">Penduduk</th>
                                <th class="text-center">Status</th>
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
                    data: 'attributes.nama_kecamatan'
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
                {
                    data: null,
                    className: 'text-center'
                },
            ],
            columnDefs: [{
                targets: 0,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                targets: 7,
                render: function(data, type, row) {
                    const attr = row.attributes || {};
                    const loginTerakhir = attr.login_terakhir;
                    const perubahanTerakhir = attr.perubahan_terakhir;

                    const batasWaktu = new Date();
                    batasWaktu.setDate(batasWaktu.getDate() - 7);

                    let loginAktif = false;
                    if (loginTerakhir && loginTerakhir !== '0000-00-00' && loginTerakhir !== '0000-00-00 00:00:00') {
                        const tglLogin = new Date(loginTerakhir);
                        if (!isNaN(tglLogin.getTime()) && tglLogin >= batasWaktu) {
                            loginAktif = true;
                        }
                    }

                    let perubahanAktif = false;
                    if (perubahanTerakhir && perubahanTerakhir !== '0000-00-00' && perubahanTerakhir !== '0000-00-00 00:00:00') {
                        const tglPerubahan = new Date(perubahanTerakhir);
                        if (!isNaN(tglPerubahan.getTime()) && tglPerubahan >= batasWaktu) {
                            perubahanAktif = true;
                        }
                    }

                    const aktif = loginAktif || perubahanAktif;

                    return aktif
                        ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-danger">Tidak Aktif</span>';
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

        $('#export-excel').on('click', function() {
            let url = new URL("{{ url('laporan/desa-aktif/export-excel') }}");
            url.searchParams.append("search", $('input[aria-controls="desaAktifTable"]').val() ?? '');
            window.open(url.href, '_blank');
        });

    })
</script>
@endpush