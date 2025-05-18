@extends('layouts.cetak.index')

@section('title', 'Data Kecamatan')

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
                <th>Kecamatan</th>
                <th">Jumlah Penduduk</th>
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
                url: `{{ config('app.databaseGabunganUrl') . '/api/v1/wilayah/penduduk-kecamatan' }}?${filter}`,
                headers: header,
                method: 'get',
                success: function(json) {
                    var no = 1;

                    json.data.forEach(function(item) {
                        var row = `
                                <tr>
                                    <td class="padat">${no}</td>
                                    <td>${item.attributes.nama_kecamatan}</td>
                                    <td>${item.attributes.penduduk_count}</td>
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
