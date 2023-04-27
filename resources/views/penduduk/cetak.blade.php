@extends('layouts.cetak.index')

@section('title', 'Data Penduduk')

@push('css')
    <style type="text/css" media="print">
        @page {
            size: landscape;
        }
    </style>
@endpush

@section('content')
    <table class="border thick" id="tabel-penduduk">
        <thead>
            <tr class="border thick">
                <th>No</th>
                <th>No. KK</th>
                <th>NIK</th>
                <th>Tag Id Card</th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>Dusun</th>
                <th>RW</th>
                <th>RT</th>
                <th>Jenis Kelamin</th>
                <th>Tempat Lahir</th>
                <th>Tanggal Lahir</th>
                <th>Umur</th>
                <th>Agama</th>
                <th>Pendidikan (dlm KK)</th>
                <th>Pekerjaan</th>
                <th>Kawin</th>
                <th>Hub. Keluarga</th>
                <th>Nama Ayah</th>
                <th>Nama Ibu</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
@stop

@push('scripts')
    <script>
        $(document).ready(function() {
            $.ajax({
                url: `{{ url('api/v1/penduduk') }}?{{ $filter }}`,
                method: 'get',
                success: function(json) {
                    var no = 1;

                    json.data.forEach(function(item) {
                        var row = `
                                <tr>
                                    <td class="padat">${no}</td>
                                    <td>${item.attributes.keluarga?.no_kk}</td>
                                    <td>${item.attributes.nik}</td>
                                    <td>${item.attributes.tag_id_card}</td>
                                    <td>${item.attributes.nama}</td>
                                    <td>${item.attributes.keluarga?.alamat}</td>
                                    <td>${item.attributes.cluster_desa?.dusun}</td>
                                    <td>${item.attributes.cluster_desa?.rw}</td>
                                    <td>${item.attributes.cluster_desa?.rt}</td>
                                    <td>${item.attributes.jenis_kelamin?.nama}</td>
                                    <td>${item.attributes.tempatlahir}</td>
                                    <td>${item.attributes.tanggalLahirId}</td>
                                    <td>${item.attributes.umur}</td>
                                    <td>${item.attributes.agama?.nama}</td>
                                    <td>${item.attributes.pendidikan_k_k?.nama}</td>
                                    <td>${item.attributes.pekerjaan?.nama}</td>
                                    <td>${item.attributes.statusPerkawinan}</td>
                                    <td>${item.attributes.penduduk_hubungan?.nama}</td>
                                    <td>${item.attributes.nama_ayah}</td>
                                    <td>${item.attributes.nama_ibu}</td>
                                    <td>${item.attributes.penduduk_status?.nama}</td>
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
