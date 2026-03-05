@extends('layouts.cetak.index')

@section('title', 'Data Penduduk Data Presisi Jaminan Sosial')

@push('css')
    <style nonce="{{ csp_nonce() }}" type="text/css" media="print">
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
                <th>NAMA</th>
                <th>JENIS BANTUAN SOSIAL YANG PERNAH DITERIMA</th>
                <th>JENIS GANGGUAN MENTAL YANG DIDERITA</th>
                <th>JENIS PENANGANAN PENDERITA GANGGUAN MENTAL</th>
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
                url: `{{ config('app.databaseGabunganUrl') . '/api/v1/data-presisi/jaminan-sosial/rtm' }}?${filter}`,
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
                                <td>${item.attributes.jns_bantuan || 'TIDAK TAHU'}</td>
                                <td>${item.attributes.jns_gangguan_mental || 'TIDAK TAHU'}</td>
                                <td>${item.attributes.terapi_gangguan_mental || 'TIDAK TAHU'}</td>
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
