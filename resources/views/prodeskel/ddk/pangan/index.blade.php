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
    document.addEventListener("DOMContentLoaded", function() {
        const dtks = $('#table-dtks').DataTable({
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
                    json.recordsTotal = json.meta.pagination.total;
                    json.recordsFiltered = json.meta.pagination.total;
                    return json.data;
                },
            },
            columnDefs: [
                { targets: '_all', className: 'text-nowrap' },
                { targets: [0, 2, 3, 4, 5, 6, 7], orderable: false, searchable: false }
            ],
            columns: [
                { className: 'details-control', orderable: false, data: null, defaultContent: '' },
                { data: "attributes.nik", render: data => data || 'N/A' },
                { data: "attributes.kepemilikan_lahan", render: data => data[0]?.jenis_lahan || 'N/A' },
                { data: "attributes.kepemilikan_lahan", render: data => data[0]?.luas_lahan || 'N/A' },
                { data: "attributes.kepemilikan_lahan", render: data => data[1]?.jenis_lahan || 'N/A' },
                { data: "attributes.kepemilikan_lahan", render: data => data[1]?.luas_lahan || 'N/A' },
                { data: "attributes.kepemilikan_lahan", render: data => data[2]?.jenis_lahan || 'N/A' },
                { data: "attributes.kepemilikan_lahan", render: data => data[2]?.luas_lahan || 'N/A' },
            ],
        });

        dtks.on('click', 'td.details-control', function() {
            const tr = $(this).closest('tr');
            const row = dtks.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(generateChildContent(row.data())).show();
                tr.addClass('shown');
            }
        });

        function generateChildContent(data) {
            const createTable = (title, items, columns) => `
                <h5>${title}</h5>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>${columns.map(col => `<th>${col}</th>`).join('')}</tr>
                    </thead>
                    <tbody>
                        ${
                            Array.isArray(items) && items.length > 0
                                ? items.map(item => `
                                    <tr>${columns.map(col => {
                                        // Convert column header to snake_case
                                        const key = col.replace(/\s+/g, '_').toLowerCase();
                                        return `<td>${item[key] ?? 'N/A'}</td>`;
                                    }).join('')}</tr>
                                `).join('')
                                : `<tr class="text-center"><td colspan="${columns.length}">Tidak ada data</td></tr>`
                        }
                    </tbody>
                </table>`;

            return `
                <div class="child-table">
                    ${createTable('Produksi Tanaman Pangan', data.attributes.produksi_tanaman_pangan || [], ['Jenis Komoditas', 'Satuan', 'Nilai Produksi', 'Pemasaran Hasil'])}
                    ${createTable('Produksi Buah-buahan', data.attributes.produksi_buah_buahan || [], ['Jenis Komoditas', 'Jumlah Pohon', 'Produksi', 'Pemasaran Hasil'])}
                    ${createTable('Produksi Tanaman Obat', data.attributes.produksi_tanaman_obat || [], ['Jenis Komoditas', 'Jumlah Pohon', 'Produksi', 'Pemasaran Hasil'])}
                    ${createTable('Produksi Tanaman Perkebunan', data.attributes.produksi_perkebunan || [], ['Jenis Komoditas', 'Jumlah Pohon', 'Produksi', 'Pemasaran Hasil'])}
                    ${createTable('Produksi Hasil Ternak', data.attributes.produksi_hasil_ternak || [], ['Jenis Komoditas', 'Jumlah Pohon', 'Produksi', 'Pemasaran Hasil'])}
                    ${createTable('Produksi Hasil Perikanan', data.attributes.produksi_perikanan || [], ['Jenis Komoditas', 'Jumlah Pohon', 'Produksi', 'Pemasaran Hasil'])}
                    <h5>Pola Makan</h5>
                    <table class="table table-sm">
                        <thead><tr><th>Kategori</th><th class="text-center">Status</th></tr></thead>
                        <tbody>
                            ${data.attributes.pola_makan_keluarga 
                                ? Object.entries(data.attributes.pola_makan_keluarga).map(([key, value]) => `<tr><td>${key}</td><td class="text-center">${value ? '✔️' : '❌'}</td></tr>`).join('')
                                : '<tr><td colspan="2">Tidak ada data</td></tr>'
                            }
                        </tbody>
                    </table>
                </div>`;
        }
    });
</script>

@endsection