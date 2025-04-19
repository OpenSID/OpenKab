@extends('layouts.cetak.index')

@section('title', 'Data Penduduk Data Presisi Pendidikan')

@push('css')
    <style nonce="{{ csp_nonce() }}"  type="text/css" media="print">
        @page {
            size: landscape;
        }
    </style>
@endpush

@section('content')
    @include('partials.breadcrumbs')
    <table class="border thick" id="tabel-pendidikan">
        <thead>
            <tr class="border thick">
                <th>NO</th>
                <th>NIK</th>
                <th>NOMOR KK</th>
                <th>NAMA</th>
                <th>TINGKAT PENDIDIKAN YANG DI TAMATKAN</th>
                <th>JENJANG PENDIDIKAN YANG SEDANG DITEMPUH</th>
                <th>KEIKUTSERTAAN KIP</th>
                <th>JENIS PENDIDIKAN KESETARAAN YANG DIIKUT</th>
                <th>TANGGAL PENGISIAN</th>
                <th>STATUS PENGISIAN</th>
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
                url: `{{ config('app.databaseGabunganUrl').'/api/v1/data-presisi/pendidikan' }}?${filter}`,
                headers: header,
                method: 'get',
                success: function(json) {
                    var no = 1;
                    json.data.forEach(function(item) {
                        var row = `
                            <tr>
                                <td class="padat">${no}</td>
                                <td>${item.attributes.nik || 'N/A'}</td>
                                <td>${item.attributes.no_kk || 'N/A'}</td>
                                <td>${item.attributes.nama || 'N/A'}</td>
                                <td>${item.attributes.pendidikan_dalam_kk || 'N/A'}</td>
                                <td>${item.attributes.pendidikan_sedang_ditempuh || 'N/A'}</td>
                                <td>${item.attributes.keikutsertaan_kip || 'N/A'}</td>
                                <td>${item.attributes.jenis_pendidikan_kesetaraan_yg_diikuti || 'N/A'}</td>
                                <td>${item.attributes.tanggal_pengisian || 'N/A'}</td>
                                <td>${item.attributes.status_pengisian || 'N/A'}</td>
                            </tr>
                            `
                        $('#tabel-pendidikan tbody').append(row)
                        no++;
                    })
                }
            })
        });
    </script>
@endpush