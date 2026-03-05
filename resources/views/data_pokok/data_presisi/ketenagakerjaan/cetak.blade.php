@extends('layouts.cetak.index')

@section('title', 'Data Penduduk Data Presisi Ketenagakerjaan')

@push('css')
    <style nonce="{{ csp_nonce() }}"  type="text/css" media="print">
        @page {
            size: landscape;
        }
    </style>
@endpush

@section('content')
    @include('partials.breadcrumbs')
    <table class="border thick" id="tabel-ketenagakerjaan">
        <thead>
            <tr class="border thick">
                <th>NO</th>
                <th>NIK</th>
                <th>NAMA KEPALA KELUARGA</th>
                <th>JUMLAH ANGGOTA</th>
                <th>JENIS PEKERJAAN</th>
                <th>TEMPAT KERJA</th>
                <th>FREKWENSI MENGIKUTI PELATIHAN SETAHUN</th>
                <th>JENIS PELATIHAN DIIKUTI SETAHUN</th>
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
                url: `{{ config('app.databaseGabunganUrl').'/api/v1/data-presisi/ketenagakerjaan/rtm' }}?${filter}`,
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
                                <td>${item.attributes.jenis_pekerjaan || 'N/A'}</td>
                                <td>${item.attributes.tempat_kerja || 'N/A'}</td>
                                <td>${item.attributes.frekwensi_mengikuti_pelatihan_setahun || 'N/A'}</td>
                                <td>${item.attributes.jenis_pelatihan_diikuti_setahun || 'N/A'}</td>
                                <td>${item.attributes.tanggal_pengisian || 'N/A'}</td>
                                <td>${item.attributes.status_pengisian || 'N/A'}</td>
                            </tr>
                            `
                        $('#tabel-ketenagakerjaan tbody').append(row)
                        no++;
                    })
                }
            })
        });
    </script>
@endpush