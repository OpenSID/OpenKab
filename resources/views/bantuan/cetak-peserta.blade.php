@extends('layouts.cetak.index')

@section('title', 'Data Peserta Bantuan')

@push('css')
    <style nonce="{{ csp_nonce() }}" type="text/css" media="print">
        @page {
            size: landscape;
        }
    </style>
@endpush

@section('content')
@include('partials.breadcrumbs')
<table class="border thick" id="tabel-peserta">
    <thead>
        <tr class="border thick">
            <th>No</th>
            <th>NIK</th>
            <th>No. KK</th>
            <th>Nama Penduduk</th>
            <th>No. Kartu Peserta</th>
            <th>Tempat Lahir</th>
            <th>Tanggal Lahir</th>
            <th>Jenis Kelamin</th>
            <th>Alamat</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
@stop

@push('scripts')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function (event) {
            const headers = @include('layouts.components.header_bearer_api_gabungan');
            var str = `{{ request()->getQueryString() ?? '' }}`;
            var filter = str.replace(/&amp;/g, '&');

            $.ajax({
                url: `{{ config('app.databaseGabunganUrl') . '/api/v1/bantuan/peserta' }}?filter[program_id]={{ $id }}&${filter}`,
                headers: headers,
                method: 'get',
                success: function (json) {
                    var no = 1;

                    json.data.forEach(function (item) {
                        var row = `
                                <tr>
                                    <td class="padat">${no}</td>
                                    <td>${item.attributes.nik ?? '-'}</td>
                                    <td>${item.attributes.no_kk ?? '-'}</td>
                                    <td>${item.attributes.kartu_nama ?? '-'}</td>
                                    <td>${item.attributes.no_id_kartu ?? '-'}</td>
                                    <td>${item.attributes.kartu_tempat_lahir ?? '-'}</td>
                                    <td>${item.attributes.kartu_tanggal_lahir ?? '-'}</td>
                                    <td>${item.attributes.jenis_kelamin?.nama ?? '-'}</td>
                                    <td>${item.attributes.kartu_alamat ?? '-'}</td>
                                    <td>${item.attributes.keterangan?.nama ?? '-'}</td>
                                </tr>
                            `;

                        $('#tabel-peserta tbody').append(row);
                        no++;
                    });
                }
            });
        });
    </script>
@endpush