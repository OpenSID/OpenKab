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
    <table class="border thick" id="tabel-sandang">
        <thead>
            <tr class="border thick">
                <th>NO</th>
                <th>NIK</th>
                <th>NOMOR KK</th>
                <th>NAMA</th>
                <th>JML PAKAIAN YANG DIMILIKI</th>
                <th>FREKWENSI BELI PAKAIAN PERTAHUN</th>
                <th>JENIS PAKAIAN</th>
                <th>FREKWENSI GANTI PAKAIAN</th>
                <th>TMPT CUCI PAKAIAN</th>
                <th>JML PAKAIAN BERAGAM</th>
                <th>JML PAKAIAN SEMBAHYANG</th>
                <th>JML PAKAIAN KERJA</th>
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
                url: `{{ config('app.databaseGabunganUrl').'/api/v1/data-presisi/sandang/rtm' }}?${filter}`,
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
                                <td>${item.attributes.jml_pakaian_yg_dimiliki || 'N/A'}</td>
                                <td>${item.attributes.frekwensi_beli_pakaian_pertahun || 'N/A'}</td>
                                <td>${item.attributes.jenis_pakaian || 'N/A'}</td>
                                <td>${item.attributes.frekwensi_ganti_pakaian || 'N/A'}</td>
                                <td>${item.attributes.tmpt_cuci_pakaian || 'N/A'}</td>
                                <td>${item.attributes.jml_pakaian_seragam || 'N/A'}</td>
                                <td>${item.attributes.jml_pakaian_sembahyang || 'N/A'}</td>
                                <td>${item.attributes.jml_pakaian_kerja || 'N/A'}</td>                                
                            </tr>
                            `

                        $('#tabel-sandang tbody').append(row)
                        no++;
                    })
                }
            })
        });
    </script>
@endpush
