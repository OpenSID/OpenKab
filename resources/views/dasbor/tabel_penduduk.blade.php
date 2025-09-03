<div id="tabel_penduduk_block" class="col-12">
    @include('dasbor.data-desa')
</div>

@push('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            let urlPenduduk = new URL(
                "{{ config('app.databaseGabunganUrl') . '/api/v1/wilayah/penduduk' }}");
            const urlDetailLink = `{{ url('penduduk') }}`;
            const pendudukDatatable = $('#summary-penduduk').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                // ordering: false,
                searchPanes: {
                    viewTotal: false,
                    columns: [0]
                },
                ajax: {
                    url: urlPenduduk,
                    method: 'get',
                    data: function(row) {
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            "filter[search]": row.search.value,
                            "filter[kode_kabupaten]": $("#filter_kabupaten").val(),
                            "filter[kode_kecamatan]": $("#filter_kecamatan").val(),
                            "filter[kode_desa]": $("#filter_desa").val(),
                            // sort by multiple column
                            "sort": (row.order.length > 0 && row.columns[row.order[0].column].name)
                                ? (row.order[0].dir == 'asc' ? '' : '-')+ row.columns[row.order[0].column].name
                                : '-surat_count,-artikel_count,-traffic_count',
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
                        targets: 0,
                        render: function(data, type, row, meta) {
                            var PageInfo = $('#summary-penduduk').DataTable().page
                                .info();
                            return PageInfo.start + meta.row + 1;
                        }
                    },
                    {
                        targets: [0, 1, 2],
                        orderable: false,
                        searchable: false,
                    },
                ],
                columns: [{
                        data: null,
                    },
                    {
                        data: "attributes.nama_desa",
                        name: "nama_desa"
                    },
                    {
                        data: "attributes.nama_kecamatan",
                        name: "nama_kecamatan"
                    },
                    {
                        data: function(data) {
                            let urlDetail = new URL(urlDetailLink);
                            urlDetail.searchParams.set('filter[kode_desa]', data
                                .attributes.kode_desa);
                            urlDetail.searchParams.set('filter[kode_kecamatan]', data
                                .attributes.kode_kecamatan);
                            urlDetail.searchParams.set('filter[kode_kabupaten]', data
                                .attributes.kode_kabupaten);
                            urlDetail.searchParams.set('filter[nama_desa]', data
                                .attributes.nama_desa);
                            urlDetail.searchParams.set('filter[nama_kecamatan]', data
                                .attributes.nama_kecamatan);
                            urlDetail.searchParams.set('filter[nama_kabupaten]', data
                                .attributes.nama_kabupaten);

                            return `<a target="_blank" href=${urlDetail.href}>${data.attributes.penduduk_count}</a>`
                        },
                        name: "penduduk_count",
                        className: 'text-center',
                        searchable: false
                    },
                    {
                        data: "attributes.surat_count",
                        name: "surat_count",
                        className: 'text-center',
                        searchable: false
                    },
                    {
                        data: "attributes.artikel_count",
                        name: "artikel_count",
                        className: 'text-center',
                        searchable: false
                    },
                    {
                        data: "attributes.traffic_count",
                        name: "traffic_count",
                        className: 'text-center',
                        searchable: false
                    },
                    {
                        data: function(data) {
                            return data.attributes.last_login ? (new Date(
                                    data.attributes.last_login))
                                .toLocaleString() : '-';
                        },
                        name: "last_login",
                        className: 'text-center',
                        searchable: false
                    },

                ],
            })
            $('#tabel_penduduk_block').change(function(event) {
                pendudukDatatable.draw();
            })
            $('#tabel_penduduk_block').trigger('change');
        });
    </script>
@endpush
