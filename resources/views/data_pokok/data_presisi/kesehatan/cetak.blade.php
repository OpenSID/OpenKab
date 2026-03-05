@extends('layouts.cetak.index')

@section('title', 'Data Penduduk Data Presisi Kesehatan')

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
                <th>NAMA KEPALA KELUARGA</th>
                <th>JUMLAH ANGGOTA RTM</th>
                <th>JNS ASURANSI</th>
                <th>JNS PENGGUNAAN ALAT KONTRASEPSI</th>
                <th>JNS PENYAKIT YANG DIDERITA</th>
                <th>KUNJUNGAN KE FASKES DALAM 1 TAHUN</th>
                <th>RAWAT INAP DALAM 1 TAHUN</th>
                <th>KUNJUNGAN KE DOKTER DALAM 1 TAHUN</th>
                <th>KONDISI FISIK SEJAK LAHIR</th>
                <th>STATUS GIZI BALITA</th>
                <th>TANGGAL PENGISIAN</th>                
            </tr>
        </thead>
        <tbody></tbody>
    </table>
@stop

@push('scripts')
    <script nonce="{{ csp_nonce() }}"  >
        document.addEventListener("DOMContentLoaded", function(event) {
            var str = `{{ $filter }}`
             var filter = str.replace(/&amp;/g, '&').replace(/undefined/g, '')
            const header = @include('layouts.components.header_bearer_api_gabungan');
            $.ajax({
                url: `{{ config('app.databaseGabunganUrl').'/api/v1/data-presisi/kesehatan/rtm' }}?${filter}`,
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
                                <td>${item.attributes.jns_ansuransi || 'N/A'}</td>
                                <td>${item.attributes.jns_penggunaan_alat_kontrasepsi || 'N/A'}</td>
                                <td>${item.attributes.jns_penyakit_diderita || 'N/A'}</td>
                                <td>${item.attributes.frekwensi_kunjungan_faskes_pertahun || 'N/A'}</td>
                                <td>${item.attributes.frekwensi_rawat_inap_pertahun || 'N/A'}</td>
                                <td>${item.attributes.frekwensi_kunjungan_dokter_pertahun || 'N/A'}</td>
                                <td>${item.attributes.kondisi_fisik_sejak_lahir || 'N/A'}</td>
                                <td>${item.attributes.status_gizi_balita || 'N/A'}</td>
                                <td>${item.attributes.tanggal_pengisian || 'N/A'}</td>                                
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