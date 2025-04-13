@extends('layouts.cetak.index')

@section('title', 'Data Penduduk Data Presisi Pangan')

@push('css')
    <style nonce="{{ csp_nonce() }}"  type="text/css" media="print">
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
                <th>NOMOR KK</th>
                <th>NAMA</th>
                <th>JENIS LAHAN</th>
                <th>LUAS LAHAN</th>
                <th>LUAS TANAM</th>
                <th>STATUS LAHAN</th>
                <th>KOMODITI UTAMA TANAMAN PANGAN</th>
                <th>KOMODITI TANAMAN PANGAN LAINNYA</th>
                <th>JUMLAH BERDASARKAN JENIS KOMODITI</th>
                <th>USIA KOMODITI</th>
                <th>JENIS PETERNAKAN</th>
                <th>JUMLAH POPULASI</th>
                <th>JENIS PERIKANAN</th>
                <th>FREKWENSI MAKANAN PERHARI</th>
                <th>FREKWENSI KONSUMSI SAYUR PERHARI</th>
                <th>FREKWENSI KONSUMSI BUAH PERHARI</th>
                <th>FREKWENSI KONSUMSI DAGING PERHARI</th>
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
                url: `{{ config('app.databaseGabunganUrl').'/api/v1/data-presisi/pangan' }}?${filter}`,
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
                                <td>${item.attributes.jenis_lahan || 'N/A'}</td>
                                <td>${item.attributes.luas_lahan || 'N/A'}</td>
                                <td>${item.attributes.luas_tanam || 'N/A'}</td>
                                <td>${item.attributes.status_lahan || 'N/A'}</td>
                                <td>${item.attributes.komoditi_utama_tanaman_pangan || 'N/A'}</td>
                                <td>${item.attributes.komoditi_tanaman_pangan_lainnya || 'N/A'}</td>
                                <td>${item.attributes.jumlah_berdasarkan_jenis_komoditi || 'N/A'}</td>
                                <td>${item.attributes.usia_komoditi || 'N/A'}</td>
                                <td>${item.attributes.jenis_peternakan || 'N/A'}</td>
                                <td>${item.attributes.jumlah_populasi || 'N/A'}</td>
                                <td>${item.attributes.jenis_perikanan || 'N/A'}</td>
                                <td>${item.attributes.frekwensi_makanan_perhari || 'N/A'}</td>
                                <td>${item.attributes.frekwensi_konsumsi_sayur_perhari || 'N/A'}</td>
                                <td>${item.attributes.frekwensi_konsumsi_buah_perhari || 'N/A'}</td>
                                <td>${item.attributes.frekwensi_konsumsi_daging_perhari || 'N/A'}</td>
                                <td>${item.attributes.tanggal_pengisian || 'N/A'}</td>
                                <td>${item.attributes.status_pengisian || 'N/A'}</td>

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