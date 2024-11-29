@extends('layouts.index')

@section('title', 'Data Papan')

@section('content_header')
    <h1>Data Kepemilikan Lahan dan Produksi</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-dtks">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>NIK</th>
                                    <th>Lahan Pangan</th>
                                    <th>Luas Lahan</th>
                                    <th>Lahan Perkebunan</th>
                                    <th>Luas Lahan</th>
                                    <th>Lahan Hutan</th>
                                    <th>Luas Lahan</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            var dtks = $('#table-dtks').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ordering: false,
                searchPanes: {
                    viewTotal: false,
                    columns: [0]
                },
                ajax: {
                    url: `{{ url('api/v1/prodeskel/ddk/pangan') }}`,
                    method: 'get',
                    data: function(row) {
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            "filter[search]": row.search.value,
                            "kode_kecamatan": "{{ session('kecamatan.kode_kecamatan') ?? '' }}",
                            "config_desa": "{{ session('desa.id') ?? '' }}",
                        };
                    },
                    dataSrc: function(json) {
                        json.recordsTotal = json.meta.pagination.total
                        json.recordsFiltered = json.meta.pagination.total

                        return json.data
                    },
                },
                columnDefs: [{
                        targets: '_all',
                        className: 'text-nowrap',
                    },
                    {
                        targets: [0, 2, 3, 4, 5, 6, 7],
                        orderable: false,
                        searchable: false,
                    },
                ],
                columns: [
                    {
                        "className": 'details-control',
                        "orderable": false,
                        "data": null,
                        "defaultContent": ''
                    },
                    {
                        data: "attributes.nik",
                        orderable: false,
                        render: (data) => data || 'N/A',
                    },
                    {
                        data: "attributes.kepemilikan_lahan",
                        render: (data) => data[0]?.jenis_lahan || 'N/A',
                    },
                    {
                        data: "attributes.kepemilikan_lahan",
                        render: (data) => data[0]?.luas_lahan || 'N/A',
                    },
                    {
                        data: "attributes.kepemilikan_lahan",
                        render: (data) => data[1]?.jenis_lahan || 'N/A',
                    },
                    {
                        data: "attributes.kepemilikan_lahan",
                        render: (data) => data[1]?.luas_lahan || 'N/A',
                    },
                    {
                        data: "attributes.kepemilikan_lahan",
                        render: (data) => data[2]?.jenis_lahan || 'N/A',
                    },
                    {
                        data: "attributes.kepemilikan_lahan",
                        render: (data) => data[2]?.luas_lahan || 'N/A',
                    },
                ],
            })

            // Add event listener for opening and closing details
            dtks.on('click', 'td.details-control', function () {
                let tr = $(this).closest('tr');
                let row = dtks.row(tr);

                if (row.child.isShown()) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    // Open this row
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                }
            });

            function format(data) {
                let produksiPangan = Array.isArray(data.attributes?.produksi_tanaman_pangan)
                    ? data.attributes.produksi_tanaman_pangan
                        .map(item => `
                            <tr>
                                <td>${item.jenis_komoditas || 'N/A'}</td>
                                <td>${item.satuan || 'N/A'}</td>
                                <td>${item.nilai_produksi || 'N/A'}</td>
                                <td>${item.pemasaran_hasil || 'N/A'}</td>
                            </tr>
                        `)
                        .join('')
                    : '<tr><td colspan="4">Tidak ada data</td></tr>';

                let produksiBuah = Array.isArray(data.attributes?.produksi_buah_buahan)
                    ? data.attributes.produksi_buah_buahan
                        .map(item => `
                            <tr>
                                <td>${item.jenis_komoditas || 'N/A'}</td>
                                <td>${item.jumlah_pohon || 'N/A'}</td>
                                <td>${item.nilai_produksi || 'N/A'}</td>
                                <td>${item.pemasaran_hasil || 'N/A'}</td>
                            </tr>
                        `)
                        .join('')
                    : '<tr><td colspan="4">Tidak ada data</td></tr>';

                let produksiObat = Array.isArray(data.attributes?.produksi_tanaman_obat)
                    ? data.attributes.produksi_tanaman_obat
                        .map(item => `
                            <tr>
                                <td>${item.jenis_komoditas || 'N/A'}</td>
                                <td>${item.jumlah_pohon || 'N/A'}</td>
                                <td>${item.nilai_produksi || 'N/A'}</td>
                                <td>${item.pemasaran_hasil || 'N/A'}</td>
                            </tr>
                        `)
                        .join('')
                    : '<tr><td colspan="4">Tidak ada data</td></tr>';

                let produksiPerkebunan = Array.isArray(data.attributes?.produksi_perkebunan)
                    ? data.attributes.produksi_perkebunan
                        .map(item => `
                            <tr>
                                <td>${item.jenis_komoditas || 'N/A'}</td>
                                <td>${item.jumlah_pohon || 'N/A'}</td>
                                <td>${item.nilai_produksi || 'N/A'}</td>
                                <td>${item.pemasaran_hasil || 'N/A'}</td>
                            </tr>
                        `)
                        .join('')
                    : '<tr><td colspan="4">Tidak ada data</td></tr>';

                let produksiTernak = Array.isArray(data.attributes?.produksi_hasil_ternak)
                    ? data.attributes.produksi_hasil_ternak
                        .map(item => `
                            <tr>
                                <td>${item.jenis_komoditas || 'N/A'}</td>
                                <td>${item.jumlah_pohon || 'N/A'}</td>
                                <td>${item.nilai_produksi || 'N/A'}</td>
                                <td>${item.pemasaran_hasil || 'N/A'}</td>
                            </tr>
                        `)
                        .join('')
                    : '<tr><td colspan="4">Tidak ada data</td></tr>';

                let produksiPerikanan = Array.isArray(data.attributes?.produksi_perikanan)
                    ? data.attributes.produksi_perikanan
                        .map(item => `
                            <tr>
                                <td>${item.jenis_komoditas || 'N/A'}</td>
                                <td>${item.jumlah_pohon || 'N/A'}</td>
                                <td>${item.nilai_produksi || 'N/A'}</td>
                                <td>${item.pemasaran_hasil || 'N/A'}</td>
                            </tr>
                        `)
                        .join('')
                    : '<tr><td colspan="4">Tidak ada data</td></tr>';

                let polaMakan = data.attributes?.pola_makan_keluarga
                    ? Object.entries(data.attributes.pola_makan_keluarga)
                        .map(([kategori, status]) => `
                            <tr>
                                <td>${kategori || 'N/A'}</td>
                                <td class="text-center">${status ? '✔️' : '❌'}</td>
                            </tr>
                        `)
                        .join('')
                    : '<tr><td colspan="2">Tidak ada data</td></tr>';

                return `
                    <div class="child-table">
                        <h5>Produksi Tanaman Pangan</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Jenis Komoditas</th>
                                    <th>Satuan</th>
                                    <th>Nilai Produksi</th>
                                    <th>Pemasaran Hasil</th>
                                </tr>
                            </thead>
                            <tbody>${produksiPangan}</tbody>
                        </table>
                        
                        <h5>Produksi Buah-buahan</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Jenis Komoditas</th>
                                    <th>Jumlah Pohon</th>
                                    <th>Produksi</th>
                                    <th>Pemasaran Hasil</th>
                                </tr>
                            </thead>
                            <tbody>${produksiBuah}</tbody>
                        </table>

                        <h5>Produksi Tanaman Obat</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Jenis Komoditas</th>
                                    <th>Jumlah Pohon</th>
                                    <th>Produksi</th>
                                    <th>Pemasaran Hasil</th>
                                </tr>
                            </thead>
                            <tbody>${produksiObat}</tbody>
                        </table>

                        <h5>Produksi Tanaman Perkebunan</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Jenis Komoditas</th>
                                    <th>Jumlah Pohon</th>
                                    <th>Produksi</th>
                                    <th>Pemasaran Hasil</th>
                                </tr>
                            </thead>
                            <tbody>${produksiPerkebunan}</tbody>
                        </table>

                        <h5>Produksi Hasil Ternak</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Jenis Komoditas</th>
                                    <th>Jumlah Pohon</th>
                                    <th>Produksi</th>
                                    <th>Pemasaran Hasil</th>
                                </tr>
                            </thead>
                            <tbody>${produksiTernak}</tbody>
                        </table>

                        <h5>Produksi Hasil Perikanan</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Jenis Komoditas</th>
                                    <th>Jumlah Pohon</th>
                                    <th>Produksi</th>
                                    <th>Pemasaran Hasil</th>
                                </tr>
                            </thead>
                            <tbody>${produksiPerikanan}</tbody>
                        </table>
                        
                        <h5>Pola Makan</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>${polaMakan}</tbody>
                        </table>
                    </div>
                `;
            }

        })
    </script>
@endsection