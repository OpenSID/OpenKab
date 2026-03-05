@extends('layouts.cetak.index')

@section('title', 'Data pariwisata')

@push('css')
<style nonce="{{ csp_nonce() }}" type="text/css" media="print">
    @page {
        size: landscape;
    }
</style>
@endpush

@section('content')
@include('partials.breadcrumbs')
<table class="border thick" id="pariwisata">
    <thead>
        <tr class="border thick">            
            <th>{{ config('app.sebutanDesa') }}</th>
            <th>Jenis Hiburan</th>
            <th>Jumlah Penginapan</th>
            <th>Lokasi/Tempat/Area Wisata</th>
            <th>Keberadaan</th>
            <th>Luas (Ha)</th>
            <th>Tingkat Pemanfaatan</th>
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
            url: `{{ config('app.databaseGabunganUrl') . '/api/v1/pariwisata' }}?${filter}`,
            headers: header,
            method: 'get',
            success: function(json) {
                json.data.forEach(function(item) {
                    var row = `
                            <tr>                            
                                <td>${item.attributes.nama_desa}</td>
                                <td>${item.attributes.jenis_hiburan}</td>
                                <td>${item.attributes.jumlah_penginapan}</td>
                                <td>${item.attributes.lokasi_tempat_area_wisata}</td>
                                <td>${item.attributes.keberadaan}</td>
                                <td>${item.attributes.luas}</td>
                                <td>${item.attributes.tingkat_pemanfaatan}</td>                                
                            </tr>
                            `
                    $('#pariwisata tbody').append(row)
                })
            }
        })
    });
</script>
@endpush