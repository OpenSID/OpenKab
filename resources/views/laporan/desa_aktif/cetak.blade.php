@extends('layouts.cetak.index')

@section('title', 'Laporan Desa Aktif')

@section('content')
@include('partials.breadcrumbs')
<table class="border thick" id="tabel-desa-aktif">
    <thead>
        <tr class="border thick">
            <th class="padat">No</th>
            <th class="padat">Desa</th>
            <th class="padat">Kecamatan</th>
            <th class="padat">Artikel Terbit</th>
            <th class="padat">Jumlah Akses</th>
            <th class="padat">Jml Surat</th>
            <th class="padat">Penduduk</th>
            <th class="padat">Status</th>
        </tr>
    </thead>
    <tbody>
        @if (empty($data))
        <tr>
            <td colspan="8" class="text-center">Tidak ada data</td>
        </tr>
        @else
        @foreach ($data as $index => $item)
        <tr>
            <td class="padat">{{ ((int) $index) + 1 }}</td>
            <td>{{ $item['attributes']['nama_desa'] ?? '' }}</td>
            <td>{{ $item['attributes']['nama_kecamatan'] ?? '' }}</td>
            <td class="text-center">{{ $item['attributes']['artikel'] ?? 0 }}</td>
            <td class="text-center">{{ $item['attributes']['traffic'] ?? 0 }}</td>
            <td class="text-center">{{ $item['attributes']['surat'] ?? 0 }}</td>
            <td class="text-center">{{ $item['attributes']['penduduk'] ?? 0 }}</td>
            <td class="text-center">{{ $item['attributes']['status_aktif'] ?? 'Tidak Aktif' }}</td>
        </tr>
        @endforeach
        @endif
    </tbody>
</table>
@stop
@push('scripts')
@if (!isset($excel))
<script nonce="{{  csp_nonce() }}">
    window.print();
</script>
@endif
@endpush