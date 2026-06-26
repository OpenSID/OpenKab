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
                <th>NO</th>
                <th>NIK</th>
                <th>NAMA KEPALA KELUARGA</th>
                <th>JUMLAH ANGGOTA RTM</th>
                <th>STATUS KEPEMILIKAN</th>
                <th>LUAS LANTAI (M²)</th>
                <th>JENIS LANTAI</th>
                <th>JENIS DINDING</th>
                <th>SUMBER AIR MINUM</th>
                <th>SUMBER PENERANGAN</th>
                <th>BAHAN BAKAR UNTUK MEMASAK</th>
                <th>TEMPAT PEMBUANGAN AKHIR TINJA</th>
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
                url: `{{ config('app.databaseGabunganUrl').'/api/v1/data-presisi/papan/rtm' }}?${filter}`,
                headers: header,
                method: 'get',
                success: function(json) {
                    var no = 1;

                    json.data.forEach(function(item) {
                        var row = `
                            <tr>
                                <td class="padat">${no}</td>
                                <td>${item.attributes.nik || 'N/A'}</td>
                                <td>${item.attributes.kepala_keluarga || 'N/A'}</td>
                                <td>${item.attributes.jumlah_anggota || 'N/A'}</td>
                                <td>${item.attributes.kd_stat_bangunan_tinggal || 'N/A'}</td>
                                <td>${item.attributes.luas_lantai || 'N/A'}</td>
                                <td>${item.attributes.kd_jenis_lantai_terluas || 'N/A'}</td>
                                <td>${item.attributes.kd_jenis_dinding || 'N/A'}</td>
                                <td>${item.attributes.kd_sumber_air_minum || 'N/A'}</td>
                                <td>${item.attributes.kd_sumber_penerangan_utama || 'N/A'}</td>
                                <td>${item.attributes.kd_bahan_bakar_memasak || 'N/A'}</td>
                                <td>${item.attributes.kd_pembuangan_akhir_tinja || 'N/A'}</td>
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
