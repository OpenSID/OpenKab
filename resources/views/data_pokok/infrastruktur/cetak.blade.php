@extends('layouts.cetak.index')

@section('title', 'Data Infrastruktur')

@push('css')
<style nonce="{{ csp_nonce() }}" type="text/css" media="print">
    @page {
        size: landscape;
    }
</style>
@endpush

@section('content')
@include('partials.breadcrumbs')
<table class="border thick" id="infrastruktur">
    <thead>
        <tr class="border thick">
            <th>KATEGORI</th>
            <th>JENIS SARANA/PRASARANA</th>
            <th>KONDISI BAIK</th>
            <th>KONDISI RUSAK</th>
            <th>JUMLAH</th>
            <th>SATUAN</th>
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
            url: `{{ config('app.databaseGabunganUrl') . '/api/v1/infrastruktur' }}?${filter}`,
            headers: header,
            method: 'get',
            success: function(json) {                
                json.forEach(function(item) {
                    var row = `
                            <tr>
                                <td>${item.kategori}</td>
                                <td>${item.jenis_sarana}</td>
                                <td>${item.kondisi_baik ?? '-'}</td>
                                <td>${item.kondisi_buruk ?? '-'}</td>
                                <td>${item.jumlah ?? '-'}</td>
                                <td>${item.satuan}</td>
                            </tr>
                            `
                    $('#infrastruktur tbody').append(row)                    
                })
            }
        })
    });
</script>
@endpush