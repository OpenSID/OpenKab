@extends('layouts.cetak.index')

@section('title', 'Data Penduduk Data Presisi Seni Budaya')

@push('css')
    <style nonce="{{ csp_nonce() }}"  type="text/css" media="print">
        @page {
            size: landscape;
        }
    </style>
@endpush

@section('content')
    @include('partials.breadcrumbs')
    <table class="border thick" id="tabel-seni">
        <thead>
            <tr class="border thick">
                <th>NO</th>
                <th>NIK</th>
                <th>NOMOR KK</th>
                <th>NAMA</th>
                <th>JENIS SENI YANG DIKUASAI</th>
                <th>JUMLAH PENGHASILAN DARI SENI</th>
                <th>TANGGAL PENGISIAN</th>
                <th>STATUS PENGISISAN</th>
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
                url: `{{ config('app.databaseGabunganUrl').'/api/v1/data-presisi/seni-budaya' }}?${filter}`,
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
                                <td>${item.attributes.jenis_seni_yang_dikuasai.jenis_seni_value || 'N/A'}</td>
                                <td>
                                    ${item.attributes.jumlah_penghasilan_dari_seni && !isNaN(item.attributes.jumlah_penghasilan_dari_seni) 
                                        ? 'Rp ' + parseInt(item.attributes.jumlah_penghasilan_dari_seni).toLocaleString('id-ID') 
                                        : 'N/A'}
                                </td>
                                <td>${item.attributes.tanggal_pengisian || 'N/A'}</td>
                                <td>${item.attributes.status_pengisian || 'N/A'}</td>
                            </tr>
                            `
                        $('#tabel-seni tbody').append(row)
                        no++;
                    })
                }
            })
        });
    </script>
@endpush