@extends('layouts.index')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@push('css')
   <style nonce="{{ csp_nonce() }}" >
        .details {
            margin-left: 20px;
        }
    </style>
@endpush

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-3">
                            <a class="btn btn-sm btn-secondary" data-toggle="collapse" href="#collapse-filter" role="button"
                                aria-expanded="false" aria-controls="collapse-filter">
                                <i class="fas fa-filter"></i>
                            </a>
                            <x-print-button :print-url="route('lembaga.cetak')" table-id="table-lembaga" label="Cetak" />
                            <x-excel-download-button :download-url="config('app.databaseGabunganUrl') . '/api/v1/lembaga/download'" table-id="table-lembaga" filename="data_lembaga" />
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="collapse-filter" class="collapse">
                                <x-wilayah-filter :showButtons=false :columnClass="'col-sm'" />
                                <div class="row">
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select id="status" class="form-control select2-filter"
                                                data-option='{!! json_encode(App\Models\Enums\StatusEnum::select2()) !!}' placeholder="Pilih Status Lembaga">
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="btn-group btn-group-sm btn-block">
                                                    <button type="button" id="reset" class="btn btn-secondary"><span
                                                            class="fas fa-ban"></span></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="btn-group btn-group-sm btn-block">
                                                    <button type="button" id="filter" class="btn btn-primary"><span
                                                            class="fas fa-search"></span></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="mt-0">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-lembaga">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama {{ config('app.sebutanDesa') }}</th>
                                    <th>Kode Lembaga</th>
                                    <th>Nama Lembaga</th>
                                    <th>Ketua Lembaga</th>
                                    <th>Kategori Lembaga</th>
                                    <th>Jumlah Anggota Lembaga</th>
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

            const header = @include('layouts.components.header_bearer_api_gabungan');
            var url = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/lembaga' }}");

            var lembaga = $('#table-lembaga').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ordering: true,
                searchPanes: {
                    viewTotal: false,
                    columns: [0]
                },
                ajax: {
                    url: url,
                    headers: header,
                    method: 'get',
                    data: function(row) {
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            "filter[search]": row.search.value,
                            "filter[status]": $('#status').val(),
                            "filter[kode_kabupaten]": $('#filter_kabupaten').val(),
                            "filter[kode_kecamatan]": $('#filter_kecamatan').val(),
                            "filter[kode_desa]": $('#filter_desa').val(),
                            "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]
                                    ?.column]
                                ?.name,
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
                }, ],
                columns: [{
                        data: null,
                    },
                    {
                        data: 'attributes.nama_desa',
                        orderable: false,
                        defaultContent: '',
                    },
                    {
                        data: "attributes.kode",
                        name: "kode"
                    },
                    {
                        data: "attributes.nama",
                        name: "nama"
                    },
                    {
                        data: "attributes.nama_ketua",
                        name: "nama_ketua",
                        defaultContent: '-',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: "attributes.kategori",
                        name: "kategori",
                        defaultContent: '-',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: "attributes.anggota_count",
                        name: "anggota_count",
                        orderable: false,
                        searchable: false,
                    },
                ],
                order: [
                    [3, 'asc']
                ]
            })

            lembaga.on('draw.dt', function() {
                var PageInfo = $('#table-lembaga').DataTable().page.info();
                lembaga.column(0, {
                    page: 'current'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + PageInfo.start;
                });
            });
            $('#filter').on('click', function(e) {
                lembaga.draw();
            });

            $(document).on('click', '#reset', function(e) {
                $('#status').val('').trigger('change');
                $('#filter_kabupaten').val('').trigger('change');
                $('#filter_kecamatan').val('').trigger('change');
                $('#filter_desa').val('').trigger('change');
                lembaga.draw();
            });

            $('select.select2-filter').each(function() {
                $(this).select2({
                    width: '100%',
                    theme: 'bootstrap4',
                    placeholder: $(this).attr('placeholder'),
                    allowClear: true,
                    data: $(this).data('option') ?? null,
                })
            });
        })
    </script>
@endsection
