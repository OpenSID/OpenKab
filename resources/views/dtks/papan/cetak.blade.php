@extends('layouts.cetak.index')

@section('title', 'Data Penduduk')

@push('css')
    <style nonce="{{ csp_nonce() }}"  type="text/css" media="print">
        @page {
            size: landscape;
        }
    </style>
@endpush

@section('content')
    @include('partials.breadcrumbs')
    <table class="border thick" id="tabel-papan">
        <thead>
            <tr class="border thick">
                <th>No</th>
                <th>NIK</th>
                <th>Status Kepemilikan</th>
                <th>Luas Lantai (m²)</th>
                <th>Jenis Lantai</th>
                <th>Jenis Dinding</th>
                <th>Sumber Air Minum</th>
                <th>Sumber Penerangan</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
@stop

@push('scripts')
    <script nonce="{{ csp_nonce() }}"  >
        document.addEventListener("DOMContentLoaded", function(event) {
            var str = `{{ $filter }}`
            var filter = str.replace(/&amp;/g, '&')
            const header = @include('layouts.components.header_bearer_api_gabungan');
            $.ajax({
                url: `{{ config('app.databaseGabunganUrl').'/api/v1/presisi/papan' }}?${filter}`,
                headers: header,
                method: 'get',
                success: function(json) {
                    var no = 1;

                    json.data.forEach(function(item) {
                        var row = `
                            <tr>
                                <td class="padat">${no}</td>
                                <td>${item.attributes.nik_kepala_rtm || 'N/A'}</td>
                                <td>${item.attributes.kd_stat_bangunan_tinggal || 'N/A'}</td>
                                <td>${item.attributes.luas_lantai || 'N/A'}</td>
                                <td>${item.attributes.kd_jenis_lantai_terluas || 'N/A'}</td>
                                <td>${item.attributes.kd_jenis_dinding || 'N/A'}</td>
                                <td>${item.attributes.kd_sumber_air_minum || 'N/A'}</td>
                                <td>${item.attributes.kd_sumber_penerangan_utama || 'N/A'}</td>
                            </tr>
                            `

                        $('#tabel-papan tbody').append(row)
                        no++;
                    })
                }
            })
        });
    </script>
@endpush
