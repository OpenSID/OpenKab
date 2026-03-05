@extends('layouts.cetak.index')

@section('title', 'Data Penduduk Data Presisi Adat')

@push('css')
    <style nonce="{{ csp_nonce() }}" type="text/css" media="print">
        @page {
            size: landscape;
        }
    </style>
@endpush

@section('content')
    @include('partials.breadcrumbs')
    <table class="border thick" id="tabel-adat">
        <thead>
            <tr class="border thick">
                <th>NO</th>
                <th>NIK</th>
                <th>NOMOR KK</th>
                <th>NAMA</th>
                <th>STATUS KEANGGOTAAN</th>
                <th>FREKWENSI KEGIATAN ADAT YANG DIIKUTI</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
@stop

@push('scripts')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            var str = `{{ $filter }}`
            var filter = str.replace(/&amp;/g, '&').replace(/undefined/g, '')

            const header = @include('layouts.components.header_bearer_api_gabungan');
            $.ajax({
                url: `{{ config('app.databaseGabunganUrl') . '/api/v1/data-presisi/adat/rtm' }}?${filter}`,
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
                                <td>${item.attributes.status_keanggotaan || 'N/A'}</td>
                                <td>${item.attributes.frekwensi_mengikuti_kegiatan_setahun || 'N/A'}</td>
                            </tr>
                            `
                        $('#tabel-adat tbody').append(row)
                        no++;
                    })
                }
            })
        });
    </script>
@endpush
