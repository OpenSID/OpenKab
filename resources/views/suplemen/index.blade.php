@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Suplemen')

@section('content_header')
    <h1>Data Suplemen</h1>
@stop

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
                            <a href="{{ route('suplemen.create') }} ">
                                <button type="button" class="btn btn-primary btn-sm"><i class="far fa-plus-square"></i>
                                    Tambah</button>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="collapse-filter" class="collapse">
                                <div class="row">
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Sasaran</label>
                                            <select class="select2 form-control-sm width-100" id="sasaran" name="sasaran"
                                                data-placeholder="Semua Sasaran">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select class="select2 form-control-sm width-100" id="status" name="status"
                                                data-placeholder="Semua Status">
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
                        <table class="table table-striped" id="suplemen">
                            <thead>
                                <tr>
                                <th class="padat">NO</th>
                                <th class="padat">AKSI</th>
                                <th>NAMA DATA</th>
                                <th>JUMLAH TERDATA</th>
                                <th>SASARAN</th>
                                <th width="30%">KETERANGAN</th>
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
    <script nonce="{{ csp_nonce() }}"  >
    document.addEventListener("DOMContentLoaded", function(event) {
        var suplemen = $('#suplemen').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            searchPanes: {
                viewTotal: false,
                columns: [0]
            },
            ajax: {
                url: `{{ url('api/v1/suplemen') }}`,
                method: 'get',
                data: function(row) {
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[search]": row.search.value,
                        "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]
                            ?.name,
                        "filter[sasaran]": $("#sasaran").val(),
                        "filter[status]": $("#status").val(),
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
                    targets: [0, 1, 2, 3],
                    orderable: false,
                    searchable: false,
                },
            ],
            columns: [{
                    data: null,
                },
                {
                    data: 'aksi',
                    name: 'aksi',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'nama',
                    name: 'nama',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'terdata_count',
                    name: 'terdata_count',
                    searchable: false,
                    orderable: false,
                    class: 'padat'
                },
                {
                    data: 'sasaran',
                    name: 'sasaran',
                    searchable: true,
                    orderable: true,
                    class: 'padat'
                },
                {
                    data: 'keterangan',
                    name: 'keterangan',
                    searchable: true,
                    orderable: true
                },
            ],
            order: [
                [2, 'asc']
            ]
        })

        suplemen.on('draw.dt', function() {
            var PageInfo = $('#suplemen').DataTable().page.info();
            suplemen.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });

        $('#sasaran').select2({
            theme: 'bootstrap4',
            minimumResultsForSearch: -1,
            ajax: {
                url: '{{ url('api/v1/suplemen') }}/sasaran/',
                dataType: 'json',
                processResults: function(response) {
                    return {
                        results: response.data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.nama
                            }
                        })
                    };
                }
            },
        });

        $('#status').select2({
            theme: 'bootstrap4',
            minimumResultsForSearch: -1,
            ajax: {
                url: '{{ url('api/v1/suplemen') }}/status/',
                dataType: 'json',
                processResults: function(response) {
                    return {
                        results: response.data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.nama
                            }
                        })
                    };
                }
            },
        });

        $('#filter').on('click', function(e) {
            suplemen.draw();
        });

        $(document).on('click', '#reset', function(e) {
            e.preventDefault();
            $('#sasaran').val('').change();
            $('#status').val('').change();

            suplemen.ajax.reload();
        });
    })
    </script>
@endsection
