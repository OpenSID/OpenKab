@extends('layouts.cetak.index')

@section('title', 'Data Keluarga')

@push('css')
    <style nonce="{{ csp_nonce() }}" type="text/css" media="print">
        @page {
            size: landscape;
        }
    </style>
@endpush

@section('content')
    @include('partials.breadcrumbs')
    <table class="border thick" id="tabel-penduduk">
        <thead>
            <tr class="border thick">
                <th>No</th>
                <th>No. KK</th>
                <th>Kepala Keluarga</th>
                <th>NIK</th>
                <th>Tag ID Card</th>
                <th>Jumlah Anggota</th>
                <th>Jenis Kelamin</th>
                <th>Alamat</th>
                <th>Dusun</th>
                <th>RW</th>
                <th>RT</th>
                <th>Tgl Terdaftar</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
@stop

@push('scripts')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            const header = @include('layouts.components.header_bearer_api_gabungan');
            var str = `{{ $filter }}`
            var filter = str.replace(/&amp;/g, '&')

            $.ajax({
                url: `{{ config('app.databaseGabunganUrl') . '/api/v1/keluarga' }}?${filter}`,
                headers: header,
                method: 'get',
                success: function(json) {
                    var no = 1;

                    json.data.forEach(function(item) {
                        var row = `
                                <tr>
                                    <td class="padat">${no}</td>
                                    <td>${item.attributes.no_kk}</td>
                                    <td>${item.attributes.nik_kepala}</td>
                                    <td>${item.attributes.nama_kk}</td>
                                    <td>${item.attributes.tag_id_card}</td>
                                    <td>${item.attributes.jumlah_anggota}</td>
                                    <td>${item.attributes.jenis_kelamin}</td>
                                    <td>${item.attributes.alamat}</td>
                                    <td>${item.attributes.dusun}</td>
                                    <td>${item.attributes.rw}</td>
                                    <td>${item.attributes.rt}</td>
                                    <td>${item.attributes.tgl_terdaftar}</td>
                                </tr>
                            `

                        $('#tabel-penduduk tbody').append(row)
                        no++;
                    })
                }
            })
        });
    </script>
@endpush
